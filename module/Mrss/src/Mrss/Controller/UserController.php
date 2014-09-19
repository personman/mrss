<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Entity\User as UserEntity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\AbstractForm;
use Mrss\Form\Fieldset\User as UserForm;

class UserController extends AbstractActionController
{
    /**
     * For admins editing user accounts
     *
     * @return array|\Zend\Http\Response
     * @throws \Exception
     */
    public function editAction()
    {
        $id = $this->params('id');
        /** @var \Mrss\Model\User $userModel */
        $userModel = $this->getServiceLocator()->get('model.user');
        $collegeModel = $this->getServiceLocator()->get('model.college');

        if ($id == 'add' || (empty($id) && empty($_POST['user']['id']))) {
            $user = new UserEntity();
            $user->setId('add');
            $user->setPassword('nothing');
            $user->setRole('data');

            if ($collegeId = $this->params('college')) {
                $college = $collegeModel->find($collegeId);
            } else {
                $college = $this->currentCollege();
            }

            $user->setCollege($college);
        } else {
            if (empty($id)) {
                $id = $_POST['user']['id'];
            }

            if (empty($id)) {
                throw new \Exception('User ID is required');
            }

            $user = $userModel->find($id);
        }


        $form = $this->getUserForm($user);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // Double check that they're not trying to slip a user into
                // another college
                if (!$this->isAllowed('adminMenu', 'view')) {
                    $currentCollege = $this->currentCollege();
                    if ($user->getCollege()->getId() != $currentCollege->getId()) {
                        $this->flashMessenger()
                            ->addErrorMessage('There was a problem creating the user.');
                        return $this->redirect()->toRoute('institution/users');
                    }
                }

                // Check to see if a user with this email already exists
                if ($user->getId() == 'add' &&
                    $existingUser = $userModel->findOneByEmail($user->getEmail())) {
                    // If so, update rather than insert
                    $user->setId($existingUser->getId());
                    $user->addStudies($existingUser->getStudies());
                    $user->setPassword($existingUser->getPassword());
                }

                // Assign the user to this study
                $user->addStudy($this->currentStudy());

                // Save
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('User saved.');
                //return $this->redirect()->toRoute('admin');
                return $this->redirect()->toRoute('institution/users');
            } else {
                $this->flashMessenger()->addErrorMessage('Correct errors below.');
            }

        }

        return array(
            'user' => $user,
            'form' => $form
        );
    }

    /**
     * For users editing their own account
     */
    public function accounteditAction()
    {
        if (!empty($_GET['usersstudies'])) {
            $start = microtime(1);
            $this->populateUserStudies();
            $el = round(microtime(1) - $start, 3);
            echo $el; die('s userstudies done');
        }

        $userModel = $this->getServiceLocator()->get('model.user');

        // Is the user editing themselves, or someone else?
        $someoneElse = false;
        if ($this->params()->fromRoute('id')) {
            $userId = $this->params()->fromRoute('id');
            $someoneElse = true;
        } elseif ($this->params()->fromPost('user')) {
            $postUser = $this->params()->fromPost('user');
            $userId = $postUser['id'];
            $someoneElse = true;
        } else {
            $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        }

        /** @var \Mrss\Entity\User $user */
        $user = $userModel->find($userId);

        // Make sure the user exists and belongs to this college
        $currentCollege = $this->currentCollege();
        if (empty($user) || $user->getCollege()->getId() != $currentCollege->getId()) {
            $this->flashMessenger()->addErrorMessage('User not found.');
            return $this->redirect()->toUrl('/institution/users');
        }


        $form = $this->getUserForm($user);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // Are they deleting?
                $buttons = $this->params()->fromPost('buttons');
                if (!empty($buttons['delete'])) {
                    // If the use belongs to more than one study, just remove them
                    // from this study
                    if (count($user->getStudies()) > 1) {
                        $user->removeStudy($this->currentStudy());
                    } else {
                        $userModel->delete($user);
                    }

                    $this->getServiceLocator()->get('em')->flush();

                    $this->flashMessenger()->addSuccessMessage('User deleted.');
                    return $this->redirect()->toRoute('institution/users');
                }

                // Save 'em
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Account saved.');

                // Where to redirect?
                if (true) {
                    $route = 'institution/users';
                } else {
                    $route = 'account';
                }

                return $this->redirect()->toRoute($route);
            } else {
                $this->flashMessenger()->addErrorMessage('Correct errors below.');
            }
        }

        return array(
            'form' => $form,
            'someoneElse' => $someoneElse,
            'user' => $user
        );
    }

    public function accountAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        return array(
            'user' => $user
        );
    }

    protected function getUserForm($user)
    {
        $form = new AbstractForm('user');

        $adminControls = $this->isAllowed('adminMenu', 'view');
        $em = $this->getServiceLocator()->get('em');

        $fieldset = new UserForm('user', false, $adminControls, $em);
        $fieldset->add(
            array(
                'name' => 'id',
                'type' => 'hidden'
            )
        );

        // Is the user editing themselves?
        $currentUser = $this->zfcUserAuthentication()->getIdentity();
        if ($user->getId() != $currentUser->getId()) {
            $includeDelete = true;
        } else {
            $includeDelete = false;
        }

        $fieldset->setUseAsBaseFieldset(true);
        $form->add($fieldset);
        $form->add($form->getButtonFieldset('Save', false, $includeDelete));

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\User'
            )
        );
        $form->bind($user);

        return $form;
    }

    protected function populateUserStudies()
    {
        $collegeModel = $this->getServiceLocator()->get('model.college');
        foreach ($collegeModel->findAll() as $college) {
            foreach ($college->getSubscriptions() as $subscription) {
                $study = $subscription->getStudy();
                foreach ($college->getUsers() as $user) {
                    $user->addStudy($study);
                }
            }
        }

        $this->getServiceLocator()->get('em')->flush();
    }
}
