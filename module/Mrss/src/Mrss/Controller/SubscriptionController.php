<?php

namespace Mrss\Controller;

use Mrss\Entity\User;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Form\Payment;
use Mrss\Form\SubscriptionInvoice;
use Mrss\Form\SubscriptionPilot;
use Mrss\Form\SubscriptionSystem;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Session\Container;
use Mrss\Form\Subscription as SubscriptionForm;
use Mrss\Form\Fieldset\Agreement;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mail\Message;

/**
 * Class SubscriptionController
 *
 * @package Mrss\Controller
 */
class SubscriptionController extends AbstractActionController
{
    protected $sessionContainer;

    protected $passwordService;

    /**
     * @var \Mrss\Entity\Study
     */
    protected $study;

    public function addAction()
    {
        $form = new SubscriptionForm;

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // If the form is valid, stash it in the session and go to
                // the agreement page
                $this->saveSubscriptionToSession($form->getData());
                $this->saveTransIdToSession(uniqid());

                return $this->redirect()->toRoute('subscribe/user-agreement');
            } else {
                $this->flashMessenger()->addErrorMessage(
                    "Please correct the problems below."
                );
            }
        }

        return array(
            'form' => $form
        );
    }

    /**
     * Users should only arrive at this page after filling out a valid subscription
     * form.
     *
     * @return array
     */
    public function agreementAction()
    {
        $this->checkSubscriptionIsInProgress();

        // Offer code
        if ($this->currentStudy()->hasOfferCode()) {
            $offerCodes = $this->currentStudy()->getOfferCodesArray();
        } else {
            $offerCodes = array();
        }

        $form = new Form('agreement');
        $fieldset = new Agreement(
            $this->currentStudy()->getDescription(),
            $offerCodes
        );

        $form->add($fieldset);

        // Add continue button
        $fieldset = new Fieldset('submit');
        $fieldset->setAttribute('class', 'well');

        $fieldset->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'btn btn-primary',
                    'value' => 'Continue'
                )
            )
        );

        $form->add($fieldset);

        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // Save the digital signature and title
                $formData = $form->getData();
                $agreementData = $formData['agreement'];
                $this->saveAgreementToSession($agreementData);

                // Was there a valid offer code?
                if (!empty($agreementData['offerCode'])) {
                    $this->flashMessenger()->addSuccessMessage(
                        "Your offer code will be applied to your price."
                    );
                }

                // Once they've agreed to the terms, redirect to the payment page
                return $this->redirect()->toRoute('subscribe/payment');
            } else {
                $this->flashMessenger()->addErrorMessage(
                    "Please correct the problems below."
                );
            }
        }

        return array(
            'form' => $form,
            'subscription' => $this->getSubscriptionFromSession()
        );
    }

    public function paymentAction()
    {
        $this->checkSubscriptionIsInProgress();
        $this->checkEnrollmentIsOpen();

        // Catch subscription completion via credit card
        if ($this->params()->fromQuery('UPAY_SITE_ID')) {
            // Find the postback from the payment queue
            $transId = $this->getTransIdFromSession();
            $paymentModel = $this->getServiceLocator()->get('model.payment');
            $payment = $paymentModel->findByTransId($transId);

            // The payment postback should be a success
            $postback = $payment->getPostback();
            if (!empty($postback['pmt_status'])
                && $postback['pmt_status'] == 'success') {
                $payment->setProcessed(true);
                $payment->setProcessedDate(new \DateTime('now'));
                $paymentModel->save($payment);
            } else {
                // Something went wrong
                // Either there's no postback record matching the transId
                // Or the postback payment status was failure
                $message = new Message();
                $message->addFrom('dfergu15@jccc.edu', 'Danny Ferguson');
                $message->addTo('dfergu15@jccc.edu');
                $message->setSubject("Postback issue");
                $body = "Something went wrong while processing a postback: ";
                $body .= "\n" . print_r($postback, 1);
                $body .= "\n" . print_r($_REQUEST, 1);
                $body .= "\n" . print_r($payment, 1);

                $message->setBody($body);
                $this->getServiceLocator()->get('mail.transport')->send($message);
            }


            // Complete the subscription
            $this->completeSubscription(
                $this->getSubscriptionFromSession(),
                array('paymentType' => 'creditCard'),
                false
            );

            $this->flashMessenger()->addSuccessMessage(
                "Payment processed."
            );
            return $this->redirect()->toRoute('subscribe/complete');
        }

        // Show payment forms

        // Get the uPay info from the study config
        $uPaySiteId = $this->currentStudy()->getUPaySiteId();
        $uPayUrl = $this->currentStudy()->getUPayUrl();

        // Get this dynamically based on study and date
        $amount = $this->currentStudy()->getCurrentPrice();

        // Check for offer code
        $agreement = $this->getAgreementFromSession();
        if (!empty($agreement['offerCode'])) {
            if ($this->currentStudy()->checkOfferCode($agreement['offerCode'])) {
                $amount = $this->currentStudy()
                    ->getOfferCodePrice($agreement['offerCode']);
            }
        }

        // Check other studies for subscriptions and give a discount
        $service = $this->getServiceLocator()->get('service.nhebisubscriptions');
        $year = $this->getCurrentYear();
        $subscription = $this->getSubscriptionFromSession();
        $ipeds = $subscription['institution']['ipeds'];

        $studyId = $this->currentStudy()->getId();
        if ($studyId == 2) {
            $currentStudyCode = 'mrss';
        } elseif ($studyId == 3) {
            $currentStudyCode = 'workforce';
        }
        $service->setCurrentStudyCode($currentStudyCode);

        $discount = $service->checkForDiscount($year, $ipeds);
        $amount = $amount - $discount;


        // Calculate the validation key for uPay/TouchNet
        $transId = $this->getTransIdFromSession();
        // @todo: put this in the db, too:
        $val = 'kdifvn3e9oskndfk';
        $validation_key = $val . $transId . $amount;
        $validation_key = md5($validation_key);
        $val = base64_encode(pack('H*', $validation_key));

        $ccForm = new Payment($uPaySiteId, $uPayUrl, $amount, $transId, $val);

        $invoiceForm = new SubscriptionInvoice();
        $invoiceForm->setAttribute('action', '/subscribe/invoice');

        $systemForm = new SubscriptionSystem();
        $systemForm->setAttribute('action', '/subscribe/system');

        $pilotForm = new SubscriptionPilot();
        $pilotForm->setAttribute('action', '/subscribe/invoice');

        return array(
            'ccForm' => $ccForm,
            'invoiceForm' => $invoiceForm,
            'systemForm' => $systemForm,
            'pilotForm' => $pilotForm
        );
    }

    public function systemAction()
    {
        $this->checkSubscriptionIsInProgress();
        $this->checkEnrollmentIsOpen();

        $systemForm = new SubscriptionSystem();
        $systemForm->setAttribute('action', '/subscribe/system');

        if ($this->getRequest()->isPost()) {
            $systemForm->setData($this->params()->fromPost());

            if ($systemForm->isValid()) {
                $this->completeSubscription(
                    $this->getSubscriptionFromSession(),
                    $systemForm->getData(),
                    true
                );

                $this->flashMessenger()->addSuccessMessage(
                    "Thank you for subscribing. "
                );

                return $this->redirect()->toRoute('subscribe/complete');
            }
        }
    }

    public function completeAction()
    {

    }

    public function invoiceAction()
    {
        $this->checkSubscriptionIsInProgress();
        $this->checkEnrollmentIsOpen();

        $invoiceForm = new SubscriptionInvoice();

        // Handle form submissions
        if ($this->getRequest()->isPost()) {
            $invoiceForm->setData($this->params()->fromPost());

            if ($invoiceForm->isValid()) {
                $this->completeSubscription(
                    $this->getSubscriptionFromSession(),
                    $invoiceForm->getData(),
                    true
                );

                $this->flashMessenger()->addSuccessMessage(
                    "Thank you for subscribing. "
                );

                return $this->redirect()->toRoute('subscribe/complete');
            }
        }
    }

    public function pilotAction()
    {
        $this->checkSubscriptionIsInProgress();
        $this->checkPilotIsOpen();

        $pilotForm = new SubscriptionPilot();

        // Handle form submissions
        if ($this->getRequest()->isPost()) {
            $pilotForm->setData($this->params()->fromPost());

            if ($pilotForm->isValid()) {
                $this->completeSubscription(
                    $this->getSubscriptionFromSession(),
                    $pilotForm->getData(),
                    true
                );

                $this->flashMessenger()->addSuccessMessage(
                    "Thank you for subscribing. "
                );

                return $this->redirect()->toRoute('subscribe/complete');
            }
        }
    }

    public function postbackAction()
    {
        // For dev, log what the post includes
        $filename = 'postback.log';
        $logger = new \Zend\Log\Logger;
        $writer = new \Zend\Log\Writer\Stream($filename);
        $logger->addWriter($writer);

        $message = "Postback received: \n";
        $message .= date('r') . "\n";

        $message .= print_r($_REQUEST, 1);

        $logger->info($message);

        // Save the postback to the db
        $payment = new \Mrss\Entity\Payment;
        $payment->setPostback($_REQUEST);

        $transId = $this->params()->fromPost('EXT_TRANS_ID');
        $payment->setTransId($transId);
        $payment->setProcessed(false);

        $paymentModel = $this->getServiceLocator()->get('model.payment');
        $paymentModel->save($payment);

        die('ok');
    }

    /**
     * For allowing other NHEBI apps to see if the college has a subscription
     * (for discounts)
     */
    public function checkAction()
    {
        // Params
        $year = $this->params()->fromQuery('year');
        $ipeds = $this->params()->fromQuery('ipeds');

        // Debug
        $test = $this->params()->fromQuery('test');
        if ($test) {
            $service = $this->getServiceLocator()->get('service.nhebisubscriptions');
            $service->setCurrentStudyCode('test');

            $discount = $service->checkForDiscount($year, $ipeds);
            var_dump($discount);
            die;
        }

        $checker = $this->getServiceLocator()->get('service.nhebisubscriptions.mrss');
        $checker->setStudyId($this->currentStudy()->getId());


        $result = $checker->checkSubscription($year, $ipeds);

        return new JsonModel(array('subscribed' => $result));
    }

    public function checkSubscriptionIsInProgress()
    {
        if (!$sub = $this->getSubscriptionFromSession()) {
            throw new \Exception('Subscription not present in session.');
        }
    }

    /**
     * Make sure no one is able to enroll when enrollment is closed
     *
     * @throws \Exception
     */
    public function checkEnrollmentIsOpen()
    {
        // If pilot is open, then allow enrollment, even if enrollement is closed
        if ($this->currentStudy()->getPilotOpen()) {
            return true;
        }

        if (!$this->currentStudy()->getEnrollmentOpen()) {
            throw new \Exception('Enrollment is not open for this study');
        }
    }

    public function checkPilotIsOpen()
    {
        if (!$this->currentStudy()->getPilotOpen()) {
            throw new \Exception('Pilot is not open for this study');
        }
    }

    public function saveSubscriptionToSession($subscriptionForm)
    {
        $this->getSessionContainer()->subscribeForm = $subscriptionForm;
    }

    public function getSubscriptionFromSession()
    {
        return $this->getSessionContainer()->subscribeForm;
    }

    public function saveTransIdToSession($transId)
    {
        $this->getSessionContainer()->transId = $transId;
    }

    public function getTransIdFromSession()
    {
        return $this->getSessionContainer()->transId;
    }

    public function saveAgreementToSession($agreementForm)
    {
        $this->getSessionContainer()->agreement = $agreementForm;
    }

    public function getAgreementFromSession()
    {
        return $this->getSessionContainer()->agreement;
    }

    public function getSessionContainer()
    {
        if (empty($this->sessionContainer)) {
            $this->sessionContainer = new Container('subscribe');
        }

        return $this->sessionContainer;
    }

    /**
     * Complete the subscription, creating college and users as needed
     *
     * @param $subscriptionForm
     * @param $paymentForm
     * @param bool $sendInvoice
     */
    public function completeSubscription(
        $subscriptionForm,
        $paymentForm,
        $sendInvoice = false
    ) {
        // Create or fetch the college
        $institutionForm = $subscriptionForm['institution'];
        $college = $this->createOrUpdateCollege($institutionForm);

        // Create the observation
        $observation = $this->createOrUpdateObservation($college);

        // Create the subscription record with payment info
        $subscription = $this->createOrUpdateSubscription(
            $paymentForm,
            $college,
            $observation
        );

        // Create the users, if needed
        // @todo: set different roles
        
        // Admin first
        $adminContactForm = $subscriptionForm['adminContact'];
        $adminUser = $this->createOrUpdateUser($adminContactForm, 'user', $college);

        // Now data user
        $dataContactForm = $subscriptionForm['dataContact'];
        $dataUser = $this->createOrUpdateUser($dataContactForm, 'user', $college);

        // Save it all to the db
        $this->getServiceLocator()->get('em')->flush();

        // Send invoice, if needed
        if ($sendInvoice) {
            $this->sendInvoice($subscription, $adminUser, $dataUser);
        }
    }

    protected function getCurrentYear()
    {
        return $this->getStudy()->getCurrentYear();
    }

    /**
     * @param $institutionForm
     * @return \Mrss\Entity\College
     */
    public function createOrUpdateCollege($institutionForm)
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $college = $collegeModel->findOneByIpeds($institutionForm['ipeds']);

        if (empty($college)) {
            $college = new \Mrss\Entity\College;
            $needFlush = true;
        }

        $hydrator = new DoctrineHydrator(
            $this->getServiceLocator()->get('em'),
            'Mrss\Entity\College'
        );
        $college = $hydrator->hydrate($institutionForm, $college);
        $collegeModel->save($college);

        if (!empty($needFlush)) {
            // Flush so we'll have an id
            $this->getServiceLocator()->get('em')->flush();
        }

        return $college;
    }

    public function createOrUpdateUser($data, $role, $college)
    {
        $email = $data['email'];

        /** @var \Mrss\Model\User $userModel */
        $userModel = $this->getServiceLocator()->get('model.user');

        $user = $userModel->findOneByEmail($email);

        if (empty($user)) {
            $user = new \Mrss\Entity\User;
            $createUser = true;
        }

        $user->setCollege($college);
        $user->setEmail($email);
        $user->setPrefix($data['prefix']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setTitle($data['title']);
        $user->setPhone($data['phone']);
        $user->setExtension($data['extension']);

        // 111111
        $user->setPassword('$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC');
        
        // set role
        $user->setRole('user');
        

        $userModel->save($user);

        // Flush to db so id is set
        $this->getServiceLocator()->get('em')->flush();

        if (!empty($createUser)) {
            // Send out email with one-time login link
            $pwService = $this->getPasswordService();
            $pwService->getOptions()
                ->setResetEmailTemplate('email/subscription/newuser');
            $pwService->getOptions()->setResetEmailSubjectLine(
                'Welcome to ' . $this->currentStudy()->getDescription()
            );

            $pwService->sendProcessForgotRequest($user->getId(), $user->getEmail());
        }

        return $user;
    }

    public function createOrUpdateSubscription(
        $paymentForm,
        College $college,
        Observation $observation
    ) {
        // Payment method
        $method = $paymentForm['paymentType'];

        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        // Make sure they're not already subscribed.
        $subscription = $subscriptionModel->findOne(
            $this->getCurrentYear(),
            $college->getId(),
            $this->getStudy()->getId()
        );

        if (empty($subscription)) {
            $subscription = new \Mrss\Entity\Subscription();
        }

        // Status: cc = complete, invoice or system = pending
        if ($method == 'creditCard') {
            $status = 'complete';
        } elseif ($method == 'system') {
            $subscription->setPaymentSystemName($paymentForm['system']);
            $status = 'pending';
        } elseif ($method == 'pilot') {
            $status = 'pilot';
        } else {
            $status = 'pending';
        }

        // Get the agreement data from the session
        $agreement = $this->getAgreementFromSession();
        $amount = $this->currentStudy()->getCurrentPrice();

        // Check for offer code
        if (!empty($agreement['offerCode'])) {
            if ($this->currentStudy()->checkOfferCode($agreement['offerCode'])) {
                $amount = $this->currentStudy()
                    ->getOfferCodePrice($agreement['offerCode']);
            }
        }

        $subscription->setYear($this->getCurrentYear());
        $subscription->setStatus($status);
        $subscription->setCollege($college);
        $subscription->setStudy($this->getStudy());
        $subscription->setPaymentMethod($method);
        $subscription->setObservation($observation);
        $subscription->setDigitalSignature($agreement['signature']);
        $subscription->setDigitalSignatureTitle($agreement['title']);
        $subscription->setPaymentAmount($amount);

        $subscriptionModel->save($subscription);

        return $subscription;
    }
    
    public function createOrUpdateObservation(\Mrss\Entity\College $college)
    {
        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        $observation = $observationModel->findOne(
            $college->getId(),
            $this->getCurrentYear()
        );

        if (empty($observation)) {
            $observation = new \Mrss\Entity\Observation;
        }

        $observation->setYear($this->getCurrentYear());
        $observation->setCollege($college);

        $observationModel->save($observation);

        return $observation;
    }

    /**
     * Get the study that they're subscribing to
     *
     * @throws \Exception
     * @return \Mrss\Entity\Study
     */
    protected function getStudy()
    {
        if (empty($this->study)) {
            $studyId = $this->currentStudy()->getId();

            /** @var \Mrss\Model\Study $studyModel */
            $studyModel = $this->getServiceLocator()->get('model.study');
            $this->study = $studyModel->find($studyId);

            if (empty($this->study)) {
                throw new \Exception("Study with id $studyId not found.");
            }
        }

        return $this->study;
    }

    protected function sendInvoice(
        \Mrss\Entity\Subscription $subscription,
        User $adminUser,
        User $dataUser
    ) {
        // Check config to see if emails are being suppressed (by Behat, probably)
        $config = $this->getServiceLocator()->get('config');
        if (!empty($config['suppressEmail'])) {
            return false;
        }

        $college = $subscription->getCollege();

        $invoice = new Message();
        $invoice->addFrom('dfergu15@jccc.edu', 'Danny Ferguson');
        $invoice->addTo('dfergu15@jccc.edu');
        $invoice->addTo('mtaylo24@jccc.edu');

        $study = $subscription->getStudy();
        $studyName = $study->getName();

        $collegeName = $college->getName();

        $year = $subscription->getYear();

        // Email subject
        if ($subscription->getPaymentMethod() == 'pilot') {
            $subjectIntro = 'Pilot';
        } else {
            $subjectIntro = 'Invoice';
        }

        $invoice->setSubject(
            "$subjectIntro: $collegeName subscribed to $studyName for $year"
        );

        $date = date('Y-m-d');

        $amountDue = number_format($subscription->getPaymentAmount(), 2);

        $invoice->setBody(
            "
            Study: {$study->getName()}
            Year: $year
            Institution: {$college->getName()}
            Amount Due: $amountDue
            Date: $date
            Address: {$college->getAddress()} {$college->getAddress2()}
            City: {$college->getCity()}
            State: {$college->getState()}
            Zip: {$college->getZip()}
            Digital Signature: {$subscription->getDigitalSignature()}
            Title: {$subscription->getDigitalSignatureTitle()}

            Admin User:
                {$adminUser->getFullName()}
                {$adminUser->getTitle()}
                {$adminUser->getEmail()}
                {$adminUser->getPhone()} {$adminUser->getExtension()}

            Data User:
                {$dataUser->getFullName()}
                {$dataUser->getTitle()}
                {$dataUser->getEmail()}
                {$dataUser->getPhone()} {$dataUser->getExtension()}
            "
        );

        $this->getServiceLocator()->get('mail.transport')->send($invoice);
    }

    public function getPasswordService()
    {
        if (!$this->passwordService) {
            $this->passwordService = $this->getServiceLocator()
                ->get('goalioforgotpassword_password_service');
        }
        return $this->passwordService;
    }
}
