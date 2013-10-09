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

        if (empty($id)) {
            $id = $_POST['user']['id'];
        }

        if (empty($id)) {
            throw new \Exception('User ID is required');
        }

        $userModel = $this->getServiceLocator()->get('model.user');
        $user = $userModel->find($id);

        $form = $this->getUserForm($user);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('User saved.');
                return $this->redirect()->toRoute('admin');
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
        $user = $this->zfcUserAuthentication()->getIdentity();
        $form = $this->getUserForm($user);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $userModel = $this->getServiceLocator()->get('model.user');
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Account saved.');
                return $this->redirect()->toRoute('account');
            } else {
                $this->flashMessenger()->addErrorMessage('Correct errors below.');
            }
        }

        return array(
            'form' => $form
        );
    }

    public function accountAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        return array(
            'user' => $user
        );
    }

    public function impersonateAction()
    {
        // The id of the user to impersonate
        $id = $this->params()->fromRoute('id');

        // Get the user, just to make sure and to have the name
        $userModel = $this->getServiceLocator()->get('model.user');
        $user = $userModel->find($id);

        if (empty($user)) {
            $this->flashMessenger()->addErrorMessage('That user does not exist.');
            return $this->redirect()->toUrl('/admin');
        }

        $name = $user->getFullName();

        // Get the impersonation service
        $impersonationService = $this->getServiceLocator()
            ->get('zfcuserimpersonate_user_service');

        // Impersonate
        $impersonationService->impersonate($id);

        // And redirect
        $this->flashMessenger()
            ->addSuccessMessage("You are now impersonating $name");
        return $this->redirect()->toUrl('/members');
    }

    protected function getUserForm($user)
    {
        $form = new AbstractForm('user');

        $fieldset = new UserForm('user', false);
        $fieldset->add(
            array(
                'name' => 'id',
                'type' => 'hidden'
            )
        );
        $fieldset->setUseAsBaseFieldset(true);
        $form->add($fieldset);
        $form->add($form->getButtonFieldset());

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\User'
            )
        );
        $form->bind($user);

        return $form;
    }
}
