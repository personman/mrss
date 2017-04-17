<?php

namespace Mrss\Controller;

use PHPExcel;
use Symfony\Component\Stopwatch\Stopwatch;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Entity\User as UserEntity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\AbstractForm;
use Mrss\Form\Fieldset\User as UserForm;
use Zend\Session\Container;
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;

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
        $passwordReset = null;

        $id = $this->params('id');
        $postUser = $this->params()->fromPost('user', array());


        $userModel = $this->getUserModel();
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $redirect = $this->params('redirect');

        if ($id == 'add' || (empty($id) && empty($postUser['id']))) {
            $user = new UserEntity();
            $user->setId('add');
            $user->setPassword('nothing');
            $user->setRole('data');

            $defaultState = $this->getStudyConfig()->default_user_state;

            // Admin-created users are always approved.
            if ($this->isAllowed('adminMenu', 'view')) {
                $defaultState = 1;
            }

            $user->setState($defaultState);

            if ($collegeId = $this->params('college')) {
                $college = $collegeModel->find($collegeId);
            } else {
                $college = $this->currentCollege();
            }

            $user->setCollege($college);
        } else {
            if (empty($id)) {
                $id = $postUser['id'];
            }

            if (empty($id)) {
                throw new \Exception('User ID is required');
            }

            $user = $userModel->find($id);

            // Display password reset links to admins
            if ($this->isAllowed('adminMenu', 'view')) {
                $passwordService = $this->getServiceLocator()
                    ->get('goalioforgotpassword_password_mapper');
                $passwordReset = $passwordService->findByUser($user->getId());
            }
        }


        $form = $this->getUserForm($user);

        // Redirect to renew if needed
        $form->addRedirect($redirect);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $post = $this->params()->fromPost();

            if (!isset($post['user']['studies'])) {
                $post['user']['studies'] = array();
            }



            $form->setData($post);

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

                    if (!empty($post['redirect'])) {
                        return $this->redirect()->toUrl('/' . $post['redirect']);
                    }


                    return $this->redirect()->toRoute('institution/users');
                }

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

                $sendWelcomeEmail = false;
                // Check to see if a user with this email already exists
                if ($user->getId() == 'add') {
                    $existingUser = $userModel->findOneByEmail($user->getEmail());
                    if ($existingUser) {
                        // If so, update rather than insert
                        $user->setId($existingUser->getId());
                        $user->setPassword($existingUser->getPassword());
                        if (!count($user->getStudies())) {
                            $user->addStudies($existingUser->getStudies());
                        }
                    } else {
                        // Assign the user to this study
                        $user->addStudy($this->currentStudy());
                        $sendWelcomeEmail = true;
                    }

                }

                // Save
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                if ($sendWelcomeEmail) {
                    $this->sendWelcomeEmail($user);
                }

                $this->flashMessenger()->addSuccessMessage('User saved.');

                if (!empty($post['redirect'])) {
                    return $this->redirect()->toUrl('/' . $post['redirect']);
                }

                return $this->redirect()->toRoute('institution/users');
            } else {
                $this->flashMessenger()->addErrorMessage('Correct errors below.');
            }

        }

        return array(
            'user' => $user,
            'form' => $form,
            'passwordReset' => $passwordReset
        );
    }

    /**
     * Let the newly created user know about their account and provide a link to set a password.
     * @param UserEntity $user
     */
    protected function sendWelcomeEmail(UserEntity $user)
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $mailer = $this->getServiceLocator()->get('mail.transport');
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        $oneTimeLogin = $renderer->serverUrl(
            $renderer->url(
                'zfcuser/resetpassword',
                array(
                    'userId' => $user->getId(),
                    'token' => $this->getPasswordResetKey($user->getId())
                )
            )
        );

        $params = array(
            //'study' => $study,
            'fullName' => $user->getFullName(),
            'userId' => $user->getId(),
            'oneTimeLogin' => $oneTimeLogin,
            'year' => $study->getCurrentYear(),
            'studyUrl' => $renderer->serverUrl('/'),
            'studyName' => $study->getDescription(),
            'resetUrl' => $renderer->serverUrl('/reset-password'),
            'contactUrl' => $renderer->serverUrl('/contact')
        );

        //prd($params);

        $emailTemplate = 'mrss/email/added-user';
        if ($configTemplate = $this->getStudyConfig()->welcome_email) {
            $emailTemplate = 'mrss/email/' . $configTemplate;
        }

        $content = $renderer->render($emailTemplate, $params);

        $fromEmail = $this->getStudyConfig()->from_email;
        //echo $content; die;

        $message = new Message();
        $message->setTo($user->getEmail());
        $message->setSubject("Welcome to " . $study->getDescription());
        $message->setFrom($fromEmail);
        //$message->addBcc('michelletaylor@jccc.edu');
        //$message->addBcc('dfergu15@jccc.edu');

        // make a header as html
        $html = new MimePart($content);
        $html->type = "text/html";
        $text = new MimePart(strip_tags($content));
        $text->type = "text/plain";
        $body = new MimeMessage();
        $body->setParts(array($text, $html));

        $message->setBody($body);
        $message->getHeaders()->get('content-type')->setType('multipart/alternative');

        $mailer->send($message);

    }

    /**
     * For users editing their own account
     */
    public function accounteditAction()
    {
        $userModel = $this->getUserModel();

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

        $redirect = $this->params('redirect');

        $form = $this->getUserForm($user);

        // Redirect to renew if needed
        $form->addRedirect($redirect);


        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $post = $this->params()->fromPost();

            if (!isset($post['user']['studies'])) {
                $post['user']['studies'] = array();
            }

            $form->setData($post);

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

                    if (!empty($post['redirect'])) {
                        return $this->redirect()->toUrl('/' . $post['redirect']);
                    }


                    return $this->redirect()->toRoute('institution/users');
                }


                /*prd($post);
                if (empty($post['studies'])) {
                    $user->removeStudy($this->currentStudy());
                } else {
                    pr($post);
                    foreach ($post['studies'] as $id) {
                        $study = $this->getStudyModel()->find($id);
                        pr($id);
                        $user->addStudy($study);
                    }
                }*/


                // Save 'em
                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Account saved.');

                if (!empty($post['redirect'])) {
                    return $this->redirect()->toUrl('/' . $post['redirect']);
                }


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

    protected function getStudyModel()
    {
        return $this->getServiceLocator()->get('model.study');
    }

    public function accountAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        return array(
            'user' => $user
        );
    }

    public function accountSettingsAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        return array(
            'user' => $user
        );
    }

    /**
     * Update data definitions user setting via ajax
     */
    public function definitionsAction()
    {
        $definitions = $this->params()->fromPost('definitions');

        if (empty($definitions)) {
            $definitions = null;
        }

        $userModel = $this->getUserModel();
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();
        $user = $userModel->find($userId);
        $user->setDataDefinitions($definitions);
        $userModel->save($user);
        $userModel->getEntityManager()->flush();

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    protected function getUserForm($user)
    {
        $form = new AbstractForm('user');

        $adminControls = $this->isAllowed('adminMenu', 'view');
        $em = $this->getServiceLocator()->get('em');

        // Can this user choose from a subset of roles?
        $roleSubset = $this->isAllowed('membership', 'view');

        $roleChoices = $this->getServiceLocator()->get('study')->user_role_choices;
        $roleChoices = explode(',', $roleChoices);

        // Is the user editing themselves?
        $currentUser = $this->zfcUserAuthentication()->getIdentity();
        if ($user->getId() != $currentUser->getId()) {
            $includeDelete = true;
            $editingSelf = false;
        } else {
            $includeDelete = false;
            $editingSelf = true;
        }

        $fieldset = new UserForm('user', false, $adminControls, $em, $roleSubset, $roleChoices, $editingSelf);
        $fieldset->add(
            array(
                'name' => 'id',
                'type' => 'hidden'
            )
        );


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

    /**
     * Allow a system admin to switch the college they're entering data for
     */
    public function switchAction()
    {
        $referer = $this->getRequest()->getHeader('Referer')->getUri();
        if (empty($referer)) {
            $referer = '/members';
        }

        $collegeId = $this->params('college_id');
        if (empty($collegeId)) {
            $collegeId = $this->params()->fromQuery('college_id');
        }

        // Clear active college and return to system overview
        if ($collegeId == 'overview') {
            $this->getSystemAdminSessionContainer()->college = null;

            return $this->redirect()->toUrl($referer);
        }

        // Make sure that this college belongs to the right system
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $college = $collegeModel->find($collegeId);
        $targetSystem = $college->getSystem();
        $user = $this->zfcUserAuthentication()->getIdentity();
        $userSystem = $user->getCollege()->getSystem();
        $role = $user->getRole();

        if (empty($targetSystem) || empty($userSystem) || !$this->isAllowed('systemSwitch', 'view')
            || $userSystem != $targetSystem) {
            throw new \Exception(
                'You do not have permission to enter data for that college'
            );
        }

        // Set the session variable
        $this->getSystemAdminSessionContainer()->college = $collegeId;


        // Redirect to the referrer
        return $this->redirect()->toUrl($referer);
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

    public function importAction()
    {
        $service = $this->getServiceLocator()->get('service.import.users');
        $form = $service->getForm();

        // Handle the form
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            takeYourTime();

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $filename = $data['file']['tmp_name'];

                $stats = $service->import($filename);

                $this->flashMessenger()->addSuccessMessage($stats);
                return $this->redirect()->toRoute('users/import');
            }
        }

        return array(
            'form' => $form
        );
    }

    /**
     * Generate a one-time login link for all users that haven't logged in.
     * Export to Excel.
     */
    public function exportLoginLinksAction()
    {
        takeYourTime();

        $sw = new Stopwatch();
        $saveEvery = 20;

        // Get all users who have never logged in
        $users = $this->getAllNewUsers();

        $excelArray = array(
            array('email', 'name', 'college', 'loginLink')
        );

        $i = 0;
        foreach ($users as $user) {
            $userId = $user->getId();

            $serverUrl = $this->getServiceLocator()
                ->get('viewhelpermanager')->get('serverUrl');

            $urlHelper = $this->getServiceLocator()
                ->get('viewhelpermanager')->get('url');

            // Build the one-time login url
            $key = $this->getPasswordResetKey($userId);


            $url = $serverUrl->__invoke(
                $urlHelper->__invoke(
                    'zfcuser/resetpassword',
                    array('userId' => $userId, 'token' => $key)
                )
            );

            $excelArray[] = array(
                $user->getEmail(),
                $user->getFullName(),
                $user->getCollege()->getName(),
                $url
            );


            $i++;

            if ($i % $saveEvery == 0) {
                $this->getServiceLocator()->get('em')->flush();
            }
        }

        $this->getServiceLocator()->get('em')->flush();

        // Export to Excel
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->fromArray($excelArray);
        foreach (range(0, 3) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $filename = 'new-' . $this->currentStudy()->getName() . '-users';

        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    public function resetAction()
    {
        $id = $this->params()->fromRoute('id');

        $key = $this->getPasswordResetKey($id);

        $this->flashMessenger()->addSuccessMessage("Password reset key generated.");

        return $this->redirect()->toRoute('users/edit', array('id' => $id));
    }

    protected function getPasswordResetKey($userId)
    {
        /** @var \GoalioForgotPassword\Service\Password $passwordService */
        $passwordService = $this->getServiceLocator()
            ->get('goalioforgotpassword_password_service');

        if ($existing = $passwordService->getPasswordMapper()->findByUser($userId)) {
            $key = $existing->getRequestKey();
        } else {
            $passwordService->cleanPriorForgotRequests($userId);
            $class = $passwordService->getOptions()->getPasswordEntityClass();

            /** @var \GoalioForgotPasswordDoctrineORM\Entity\Password $model */
            $model = new $class;

            $model->setUserId($userId);
            $model->setRequestTime(new \DateTime('now'));
            $model->generateRequestKey();
            $passwordService->getPasswordMapper()->persist($model);

            $key = $model->getRequestKey();
        }

        return $key;
    }

    /**
     * @return \Mrss\Entity\User[]
     */
    protected function getAllNewUsers()
    {
        $collegeModel = $this->getServiceLocator()->get('model.college');
        /** @var \Mrss\Entity\College[] $colleges */
        $colleges = $collegeModel->findAll();
        $study = $this->currentStudy();

        $users = array();
        foreach ($colleges as $college) {
            foreach ($college->getUsersByStudy($study) as $user) {
                $lastAccess = $user->getLastAccess();
                if (empty($lastAccess)) {
                    $users[] = $user;
                    //pr($user->getFullName() . ' ' . $user->getCollege()->getName());
                }
            }
        }

        return $users;
    }

    public function benchmarkorgAction()
    {
        $org = $this->params()->fromRoute('org');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $user->setAdminBenchmarkSorting($org);

        $userModel = $this->getUserModel();
        $userModel->save($user);
        $userModel->getEntityManager()->flush();

        $this->flashMessenger()->addSuccessMessage("Benchmark organization changed to $org.");
        return $this->redirect()->toRoute('benchmark');
    }

    public function approvalQueueAction()
    {
        if ($this->getRequest()->isPost()) {
            $usersToApprove = $this->params()->fromPost('users');
            $usersToApprove = array_keys($usersToApprove);
            $newState = 1; // Approve = 1

            $buttons = $this->params()->fromPost('buttons');
            $delete = (!empty($buttons['delete']));

            $count = 0;
            foreach ($usersToApprove as $userId) {
                if ($user = $this->getUserModel()->find($userId)) {
                    // Are we deleting or approving?
                    if ($delete) {
                        $this->getUserModel()->delete($user);
                    } else {
                        $user->setState($newState);
                        $this->getUserModel()->save($user);

                        $this->sendWelcomeEmail($user);
                    }

                    $count++;
                }
            }

            $this->getUserModel()->getEntityManager()->flush();

            $noun = 'user';
            if ($count != 1) {
                $noun .= 's';
            }

            $verb = 'approved';
            if ($delete) {
                $verb = 'deleted';
            }

            $this->flashMessenger()->addSuccessMessage("$count $noun $verb.");
            return $this->redirect()->toRoute('users/queue');
        }

        // 0 is pending approval
        $users = $this->getUserModel()->findByState(0);

        return array(
            'users' => $users
        );
    }

    protected function sendPasswordResetEmail($user)
    {
        $pwService = $this->getPasswordService();
        $pwService->getOptions()
            ->setResetEmailTemplate('email/subscription/newuser');
        $pwService->getOptions()->setResetEmailSubjectLine(
            'Welcome to ' . $this->getStudy()->getDescription()
        );

        $pwService->sendProcessForgotRequest($user->getId(), $user->getEmail());
    }

    /**
     * @return \Mrss\Model\User
     */
    protected function getUserModel()
    {
        $userModel = $this->getServiceLocator()->get('model.user');

        return $userModel;
    }

    public function unimpersonateAction()
    {
        // Clear the system admin session (fixes john's start loop bug)
        $this->getSystemAdminSessionContainer()->getManager()->getStorage()->clear('system_admin');

        // Redirect on to the vendor unimpersonate action
        return $this->redirect()->toRoute('zfcuserimpersonate/unimpersonate');
    }

    public function getSystemAdminSessionContainer()
    {
        $containerName = 'system_admin';

        if (empty($this->systemAdminSessionContainer)) {
            $container = new Container($containerName);
            $this->systemAdminSessionContainer = $container;
        }

        return $this->systemAdminSessionContainer;
    }

    protected function getStudyConfig()
    {
        $studyConfig = $this->getServiceLocator()->get('study');

        return $studyConfig;
    }
}
