<?php

namespace Mrss\Controller;

use Mrss\Entity\Subscription;
use Mrss\Entity\User;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Entity\SubscriptionDraft;
use Mrss\Entity\Payment;
use Mrss\Form\AbstractForm;
use Mrss\Form\Payment as PaymentForm;
use Mrss\Form\SubscriptionFree;
use Mrss\Form\SubscriptionInvoice;
use Mrss\Form\SubscriptionPilot;
use Mrss\Form\SubscriptionSystem;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Session\Container;
use Mrss\Form\Subscription as SubscriptionForm;
use Mrss\Form\Fieldset\Agreement;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use DateTime;
use PHPExcel;

/**
 * Class SubscriptionController
 *
 * @package Mrss\Controller
 */
class SubscriptionController extends AbstractActionController
{
    protected $sessionContainer;

    protected $passwordService;

    protected $log;

    protected $subscriptionDraftModel;

    protected $draftSubscription;

    protected $ipeds;

    /**
     * @var \Mrss\Entity\Study
     */
    protected $study;

    /**
     * A regular user can view his or her subscription details. Redirect here after
     * subscribing
     */
    public function viewAction()
    {
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $study = $this->currentStudy();


        if ($college = $this->currentCollege()) {
            $collegeId = $college->getId();
        } else {
            // Find from session if they're not logged in
            if ($ipeds = $this->getSessionContainer()->ipeds) {
                $collegeModel = $this->getServiceLocator()->get('model.college');
                $college = $collegeModel->findOneByIpeds($ipeds);
                $collegeId = $college->getId();
            }
        }

        $subscription = $subscriptionModel
            ->findCurrentSubscription($study, $collegeId);

        return array(
            'subscription' => $subscription,
        );
    }

    public function slack(
        $message,
        $room = "development",
        $icon = ":nccbp:",
        $username = "NCCBP-bot"
    ) {
        // Encode message
        $message = urlencode($message);

        $room = ($room) ? $room : "engineering";
        $username = ($username) ? $username : 'NCCBP-bot';

        $data = "payload=" . json_encode(array(
                    "channel"       =>  "#{$room}",
                    'username' => $username,
                    "text"          =>  $message,
                    "icon_emoji"    =>  $icon
                ));

        // You can get your webhook endpoint from your Slack settings
        $url = "https://hooks.slack.com/services/" .
            "T0316049N/B031HJY29/FHHvaDQ6Mgh60nXqh6i59zrC";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function addAction()
    {
        if ($this->params()->fromQuery('send')) {
            $message = new Message();
            $message->addFrom('dfergu15@jccc.edu', 'Danny Ferguson');
            $message->addTo('personman2@gmail.com');
            $message->setSubject("Email test");
            $body = "Test email";

            $this->getLog()->alert($body);
            $message->setBody($body);
            $this->getServiceLocator()->get('mail.transport')->send($message);
            die('sent');
        }

        // Are they signed in? If so, redirect them to renew
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('renew');
        }

        $form = new SubscriptionForm;

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // If the form is valid, stash it in the session and go to
                // the agreement page
                //$this->saveSubscriptionToSession($form->getData());
                $this->saveDraftSubscription($form->getData());

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
     * Join action for studies that let users enter data for free
     *
     * Sending a json object with all colleges requires 400KB, but using AJAX means a 2-3 second delay between
     * typing and the results updating. Too slow, so send the data up front.
     * @return array
     */
    public function joinFreeAction()
    {
        //$this->ge
        $form = new SubscriptionFree();

        $formHasErrors = 0;

        // If the form is submitted, they need to create a new subscription and new user
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $collegeId = $data['id'];
                $college = $this->getServiceLocator()
                    ->get('model.college')->find($collegeId);

                if (!empty($college)) {
                    //prd($form->getData());
                    // Create the observation
                    $observation = $this->createOrUpdateObservation($college);

                    $subscription = $this->createOrUpdateSubscription(
                        array('paymentType' => 'free'),
                        $college,
                        $observation
                    );

                    // create the user. Send email now or wait for approval?
                    // Set state to 0
                    $defaultRole = 'data';
                    $userData = $data['user'];
                    $defaultState = 0;
                    $user = $this->createOrUpdateUser($userData, $defaultRole, $college, $defaultState);

                    $this->getSubscriptionModel()->getEntityManager()->flush();

                    // redirect
                    return $this->redirect()->toRoute('joined');
                } else {
                    die('Unable to find institution.');
                }


            } else {
                $formHasErrors = 1;
            }
        }

        return array(
            'form' => $form,
            'allColleges' => $this->getAllColleges(),
            'formHasErrors' => $formHasErrors
        );
    }

    public function findUserAction()
    {
        $email = $this->params()->fromRoute('email');
        $userExists = false;

        if ($email) {
            /** @var \Mrss\Model\User $userModel */
            $userModel = $this->getServiceLocator()->get('model.user');

            $user = $userModel->findOneByEmail($email);

            if ($user) {
                $userExists = true;
            }
        }

        $response = array(
            'userExists' => $userExists
        );

        return new JsonModel($response);
    }

    protected function getAllColleges()
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $colleges = $collegeModel->findAll();

        $allColleges = array();
        foreach ($colleges as $college) {
            $nameAndState = $college->getName() . ' (' . $college->getState() . ')';
            if ($ipeds = $college->getIpeds()) {
                $nameAndState .= ', IPEDS: ' . $ipeds;
            }
            if ($opeId = $college->getOpeId()) {
                $nameAndState .= ', OPE: ' . $opeId;
            }
            $allColleges[] = array(
                'label' => $nameAndState,
                'id' => $college->getId()
            );
        }

        return json_encode($allColleges);
    }

    public function renewAction()
    {
        $college = $this->currentCollege();

        $form = new AbstractForm('renew');
        $form->add($form->getButtonFieldset('Renew'));

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = array(
                    'renew' => true,
                    'college_id' => $this->currentCollege()->getId()
                );

                $this->saveDraftSubscription($data);

                return $this->redirect()->toRoute('subscribe/user-agreement');
            }
        }

        return array(
            'college' => $college,
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
        if ($this->getStudy()->hasOfferCode()) {
            $offerCodes = $this->getStudy()->getOfferCodesArray();
        } else {
            $offerCodes = array();
        }

        $form = new Form('agreement');
        $fieldset = new Agreement(
            $this->getStudy()->getDescription(),
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
                $this->saveAgreementToDraftSubscription($agreementData);

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
            'subscription' => $this->getDraftSubscription()->getFormData()
        );
    }

    /**
     * When TouchNet sends the payment postback, complete the subscription
     */
    public function postbackAction()
    {
        $message = "Postback received: \n";
        $message .= date('r') . "\n";

        $message .= print_r($_REQUEST, 1);

        $this->getLog()->info($message);

        // Save the postback to the db
        $payment = new Payment;
        $payment->setPostback($_REQUEST);

        $transId = $this->params()->fromPost('EXT_TRANS_ID');

        $status = $this->params()->fromPost('pmt_status');
        if ($status == 'cancelled') {
            $this->flashMessenger()
                ->addErrorMessage('Credit card payment cancelled.');
            return $this->redirect()->toUrl('/members');
        }

        $payment->setTransId($transId);
        $payment->setProcessed(false);

        $paymentModel = $this->getServiceLocator()->get('model.payment');
        $paymentModel->save($payment);

        // Get the draft
        $draftSubscription = $this->getDraftSubscription($transId);
        $this->setDraftSubscription($draftSubscription);

        if (empty($draftSubscription)) {
            $this->getLog()
                ->alert('Unable to look up draft subscription by id: ' . $transId);
        } else {
            $this->getLog()->info(
                'New uPay payment with transId: ' . $transId
            );

            // The payment postback should be a success
            $postback = $payment->getPostback();
            if (empty($payment) ||  !empty($postback['pmt_status'])
                && $postback['pmt_status'] == 'success') {
                $payment->setProcessed(true);
                $payment->setProcessedDate(new DateTime('now'));
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

                $this->getLog()->alert($body);
                $message->setBody($body);
                $this->getServiceLocator()->get('mail.transport')->send($message);
            }

            // Complete the subscription
            $this->getLog()->info(
                "Completing subscription: "
                . print_r($this->getDraftSubscription(), true)
            );
            $this->completeSubscription(
                $this->getDraftSubscription(),
                array('paymentType' => 'creditCard'),
                false
            );

            $this->getLog()->info("Subscription completed.");
        }

        die('ok');
    }

    public function isRenewal()
    {
        // Renewal price @todo: make this dymanic
        $sub = json_decode($this->getDraftSubscription()->getFormData(), true);

        $isRenewal = false;
        if (!empty($sub['renew'])) {
            $isRenewal = true;
        } else {
            // Are they renewing via the new join form?
            $ipeds = $sub['institution']['ipeds'];
            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $college = $collegeModel->findOneByIpeds($ipeds);

            if (!empty($college)) {
                $subscriptions = $college
                    ->getSubscriptionsForStudy($this->getStudy());
            }

            if (!empty($subscriptions)) {
                $isRenewal = true;
            }
        }

        return $isRenewal;
    }

    public function paymentAction()
    {
        // Catch subscription completion via credit card
        if ($this->params()->fromQuery('UPAY_SITE_ID')) {
            $this->flashMessenger()->addSuccessMessage(
                "Payment processed."
            );
            return $this->redirect()->toRoute('subscribe/complete');
        }

        $this->checkSubscriptionIsInProgress();
        $this->checkEnrollmentIsOpen();


        // Show payment forms

        // Get the uPay info from the study config
        $uPaySiteId = $this->getStudy()->getUPaySiteId();
        $uPayUrl = $this->getStudy()->getUPayUrl();

        $amount = $this->getPaymentAmount();

        // Calculate the validation key for uPay/TouchNet
        $transId = $this->getTransIdFromSession();
        // @todo: put this in the db, too:
        $val = 'kdifvn3e9oskndfk';
        $validation_key = $val . $transId . $amount;
        $validation_key = md5($validation_key);
        $val = base64_encode(pack('H*', $validation_key));

        $ccForm = new PaymentForm($uPaySiteId, $uPayUrl, $amount, $transId, $val);

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

    public function getPaymentAmount()
    {
        // Get this dynamically based on study and date
        $amount = $this->getStudy()->getCurrentPrice();

        $isRenewal = $this->isRenewal();

        if ($isRenewal) {
            $amount = $this->getStudy()->getRenewalPrice();
        }

        // Check for offer code
        $agreement = json_decode(
            $this->getDraftSubscription()->getAgreementData(),
            true
        );
        $skipOtherDiscounts = false;
        if (!empty($agreement['offerCode'])) {
            $offerCode = $agreement['offerCode'];

            if ($this->getStudy()->checkOfferCode($offerCode)) {

                $amount = $this->getStudy()
                    ->getOfferCodePrice($offerCode);
                $skipOtherDiscounts = $this->getStudy()
                    ->getOfferCode($agreement['offerCode'])->getSkipOtherDiscounts();
            }
        }

        // Check other studies for subscriptions and give a discount
        if (!$skipOtherDiscounts) {
            $service = $this->getServiceLocator()->get('service.nhebisubscriptions');
            $year = $this->getCurrentYear();

            $ipeds = $this->getIpeds();

            $studyId = $this->getStudy()->getId();
            if ($studyId == 2) {
                $currentStudyCode = 'mrss';
            } elseif ($studyId == 3) {
                $currentStudyCode = 'workforce';
            } elseif ($studyId == 1) {
                $currentStudyCode = 'nccbp';
            } else {
                $currentStudyCode = 'unknown';
            }

            $service->setCurrentStudyCode($currentStudyCode);

            // For now, only discount the price for NCCBP
            if ($currentStudyCode == 'nccbp') {

                $discount = $service->checkForDiscount($year, $ipeds);
                $amount = $amount - $discount;
            }
        }

        return $amount;
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
                return $this->completeSubscription(
                    $this->getDraftSubscription(),
                    $systemForm->getData(),
                    true
                );

            }
        }
    }

    public function completeAction()
    {

    }

    public function joinedAction()
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
                return $this->completeSubscription(
                    $this->getDraftSubscription(),
                    $invoiceForm->getData(),
                    true
                );
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
                return $this->completeSubscription(
                    $this->getDraftSubscription(),
                    $pilotForm->getData(),
                    true
                );
            }
        }
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

        $checker = $this->getServiceLocator()->get('service.nhebisubscriptions.mrss');
        $checker->setStudyId($this->getStudy()->getId());


        $result = $checker->checkSubscription($year, $ipeds);

        return new JsonModel(array('subscribed' => $result));
    }

    public function checkSubscriptionIsInProgress()
    {
        if (!$sub = $this->getDraftSubscription()) {
            throw new \Exception(
                'You do not appear to have a subscription in progress. Please start
                over.'
            );
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
        if ($this->getStudy()->getPilotOpen()) {
            return true;
        }

        if (!$this->getStudy()->getEnrollmentOpen()) {
            throw new \Exception('Enrollment is not open for this study');
        }
    }

    public function checkPilotIsOpen()
    {
        if (!$this->getStudy()->getPilotOpen()) {
            throw new \Exception('Pilot is not open for this study');
        }
    }

    public function saveTransIdToSession($transId)
    {
        $this->getSessionContainer()->transId = $transId;
    }

    public function getTransIdFromSession()
    {
        return $this->getSessionContainer()->transId;
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
     * @param SubscriptionDraft $subscriptionDraft
     * @param $paymentForm
     * @param bool $sendInvoice
     * @param bool $redirect
     * @throws \Exception
     * @return \Zend\Http\Response
     * @internal param $subscriptionForm
     */
    public function completeSubscription(
        SubscriptionDraft $subscriptionDraft,
        $paymentForm,
        $sendInvoice = false,
        $redirect = true
    ) {
        $subscriptionForm = json_decode($subscriptionDraft->getFormData(), true);

        // Create or fetch the college
        if (empty($subscriptionForm['renew'])) {
            $institutionForm = $subscriptionForm['institution'];
            $execForm = $subscriptionForm['executive'];
            $institutionForm = array_merge($institutionForm, $execForm);
            $college = $this->createOrUpdateCollege($institutionForm);
        } else {
            $collegeId = $subscriptionForm['college_id'];
            $college = $this->getServiceLocator()
                ->get('model.college')->find($collegeId);
        }

        // Create the observation
        $observation = $this->createOrUpdateObservation($college);

        // Create the subscription record with payment info
        $subscription = $this->createOrUpdateSubscription(
            $paymentForm,
            $college,
            $observation,
            $this->getDraftSubscription()
        );

        if (empty($subscription)) {
            throw new \Exception("Unable to create subscription: " . print_r($paymentForm, 1));
        }

        // Create the users, if needed
        $defaultState = 1;

        // Admin first
        $adminUser = null;
        if (!empty($subscriptionForm['adminContact'])) {
            $adminContactForm = $subscriptionForm['adminContact'];
            $adminUser = $this
                ->createOrUpdateUser($adminContactForm, 'contact', $college, $defaultState);
        }

        // Data user overrides (if it's the same user)
        $dataUser = null;
        if (!empty($subscriptionForm['dataContact'])) {
            $dataContactForm = $subscriptionForm['dataContact'];
            $dataUser = $this
                ->createOrUpdateUser($dataContactForm, 'data', $college, $defaultState);
        }


        // Save it all to the db
        $this->getServiceLocator()->get('em')->flush();

        // Send invoice, if needed
        if ($sendInvoice) {
            $this->sendInvoice($subscription, $adminUser, $dataUser);
        }

        // Send a notification to slack
        $this->sendSlackNotification($subscription);

        // Send welcome email
        $this->sendWelcomeEmail($subscription);

        // Now clear out the draft subscription
        $this->getSubscriptionDraftModel()->delete($subscriptionDraft);
        $this->getServiceLocator()->get('em')->flush();

        // Redirect
        if ($redirect) {
            if (!empty($subscriptionForm['renew'])) {
                $message = "Thank you for renewing your membership! ";
            } else {
                $message = "Thank you for joining! Each new user should receive an ";
                $message .= "email with instructions on how to log in and set a ";
                $message .= "password.";
            }

            $this->flashMessenger()->addSuccessMessage($message);

            $method = $subscription->getPaymentMethod();
            $routeParams = array('paymentMethod' => $method);
            return $this->redirect()->toRoute('membership', $routeParams);
        }
    }

    protected function sendSlackNotification(Subscription $subscription)
    {
        $college = $subscription->getCollege()->getName();
        $state = $subscription->getCollege()->getState();
        $college .= " ($state)";
        $appName = $this->currentStudy()->getName();
        $cost = number_format($subscription->getPaymentAmount());
        $cost .= ' (' . $subscription->getPaymentMethodForDisplay() . ')';
        $message = "New $appName membership for $college. ";

        $message .= "Cost: $$cost. ";

        // Add notes about subscription totals:
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();

        $year = $subscription->getYear();

        $subscriptions = $subscriptionModel->findByStudyAndYear(
            $studyId,
            $year
        );

        // Total
        $total = 0;
        foreach ($subscriptions as $sub) {
            $total += $sub->getPaymentAmount();
        }
        $count = count($subscriptions);
        $total = number_format($total);

        //$message .= "\n";
        $message .= " $count members, $$total. ";

        // Configure channel, etc
        $map = array(
            1 => array('nccbp-website', ':nccbp:', 'NCCBP-bot'),
            2 => array('maximizing-website', ':maximizing:', 'Max-bot'),
            3 => array('workforce-website', ':workforce:', 'Workforce-bot')
        );

        if (!empty($map[$studyId])) {
            list($channel, $icon, $username) = $map[$studyId];

            $this->slack($message, $channel, $icon, $username);
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

    public function createOrUpdateUser($data, $role, $college, $state = null)
    {
        $email = $data['email'];

        /** @var \Mrss\Model\User $userModel */
        $userModel = $this->getServiceLocator()->get('model.user');

        $user = $userModel->findOneByEmail($email);

        if (empty($user)) {
            $user = new User;
            $createUser = true;

            // State (0 = pending, 1 = active, 2 = disabled)
            if ($state !== null) {
                $user->setState($state);
            }
        }

        $user->setCollege($college);
        $user->setEmail($email);
        $user->setPrefix($data['prefix']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setTitle($data['title']);
        $user->setPhone($data['phone']);
        $user->setExtension($data['extension']);
        $user->addStudy($this->getStudy());


        // 111111
        $user->setPassword('$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC');
        
        // set role
        $user->setRole($role);


        $userModel->save($user);

        // Flush to db so id is set
        $this->getServiceLocator()->get('em')->flush();

        if (!empty($createUser)) {
            // If they're already approved, send them a password reset link
            if ($state == 1) {
                $this->sendPasswordResetEmail($user);
            } else {
                // If they're not approved, notify the approver
                $this->notifyApprover($user);
            }
        }

        return $user;
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

    protected function notifyApprover(User $user)
    {
        $studyConfig = $this->getServiceLocator()->get('study');
        $approverEmail = $studyConfig->approver_email;
        $fromEmail = $studyConfig->from_email;

        $email = new Message();
        $email->addFrom($fromEmail);
        $email->addTo($approverEmail);

        $study = $this->currentStudy();
        $studyName = $study->getName();

        $collegeName = $user->getCollege()->getName();

        $email->setSubject("New user pending for $studyName");
        $userName = $user->getFullName();
        $url = $this->getServiceLocator()->get('ViewHelperManager')->get('serverUrl')
            ->__invoke('/users/queue');

        $body = "
            Name: $userName
            Email: {$user->getEmail()}
            Institution: $collegeName

            Approve users: $url
            ";

        $email->setBody($body);

        $this->getServiceLocator()->get('mail.transport')->send($email);
    }

    public function createOrUpdateSubscription(
        $paymentForm,
        College $college,
        Observation $observation,
        $draftSubscription = null
    ) {
        // Payment method
        $method = $paymentForm['paymentType'];

        // Make sure they're not already subscribed.
        $subscription = $this->getSubscriptionModel()->findOne(
            $this->getCurrentYear(),
            $college->getId(),
            $this->getStudy()->getId()
        );

        if (empty($subscription)) {
            $subscription = new Subscription();
        }

        // Status: cc = complete, invoice or system = pending
        if ($method == 'creditCard') {
            $status = 'complete';
        } elseif ($method == 'system') {
            $subscription->setPaymentSystemName($paymentForm['system']);
            $status = 'pending';
        } elseif ($method == 'free') {
            $status = 'complete';
        } elseif ($method == 'pilot') {
            $status = 'pilot';
        } else {
            $status = 'pending';
        }


        if ($method == 'free') {
            $amount = 0;
        } else {
            $amount = $this->getPaymentAmount();
        }

        $subscription->setYear($this->getCurrentYear());
        $subscription->setStatus($status);
        $subscription->setCollege($college);
        $subscription->setStudy($this->getStudy());
        $subscription->setPaymentMethod($method);
        $subscription->setObservation($observation);
        $subscription->setPaymentAmount($amount);

        if (!empty($draftSubscription)) {
            // Get the agreement data from the session
            $agreement = json_decode($draftSubscription->getAgreementData(), true);

            $subscription->setDigitalSignature($agreement['signature']);
            $subscription->setDigitalSignatureTitle($agreement['title']);
        }

        $this->getSubscriptionModel()->save($subscription);

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

    public function sendinvoiceAction()
    {
        $id = $this->params()->fromPost('id');
        $to = $this->params()->fromPost('to');

        if ($id && $sub = $this->getSubscriptionModel()->find($id)) {
            $this->sendInvoice($sub, null, null, $to);
            $this->flashMessenger()->addSuccessMessage('Invoice sent to ' . $to);
        } else {
            $this->flashMessenger()->addErrorMessage('No membership found for id: ' . $to);
        }

        $url = $this->getRequest()->getHeader('Referer')->getUri();

        if (!$url) {
            $url = '/';
        }

        return $this->redirect()->toUrl($url);
    }

    protected function sendInvoice(
        Subscription $subscription,
        User $adminUser = null,
        User $dataUser = null,
        $to = null
    ) {
        // Check config to see if emails are being suppressed (by Behat, probably)
        $config = $this->getServiceLocator()->get('config');
        if (!empty($config['suppressEmail'])) {
            return false;
        }

        $college = $subscription->getCollege();

        $invoice = new Message();
        $invoice->addFrom('dfergu15@jccc.edu', 'Danny Ferguson');

        if ($to) {
            $invoice->addTo($to);
        } else {
            $invoice->addTo('dfergu15@jccc.edu');
            $invoice->addTo('michelletaylor@jccc.edu');
        }
        $study = $subscription->getStudy();
        $studyName = $study->getName();

        $collegeName = $college->getName();

        $year = $subscription->getYear();

        $paymentMethod = $subscription->getPaymentMethod();

        // Email subject
        if ($subscription->getPaymentMethod() == 'pilot') {
            $subjectIntro = 'Pilot';
        } elseif ($paymentMethod == 'invoice') {
            $subjectIntro = 'Invoice';
        } else {
            $subjectIntro = 'Membership';
        }

        $invoice->setSubject(
            "$subjectIntro: $collegeName joined $studyName for $year"
        );

        $date = date('Y-m-d');

        $amountDue = number_format($subscription->getPaymentAmount(), 2);

        $body =
            "
            Study: {$study->getName()}
            Year: $year
            Institution: {$college->getName()}
            Amount Due: $amountDue
            Payment Method: {$subscription->getPaymentMethodForDisplay()}
            Date: $date
            Address: {$college->getAddress()} {$college->getAddress2()}
            City: {$college->getCity()}
            State: {$college->getState()}
            Zip: {$college->getZip()}
            Digital Signature: {$subscription->getDigitalSignature()}
            Title: {$subscription->getDigitalSignatureTitle()}

            ";

        if ($adminUser && $dataUser) {
                $body .= "
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
            ";
        }
        $invoice->setBody($body);

        $this->getServiceLocator()->get('mail.transport')->send($invoice);
    }

    protected function sendWelcomeEmail(Subscription $subscription)
    {
        $from_email = $this->currentStudy(false)->getConfig()->from_email;
        $cc_email = $this->currentStudy(false)->getConfig()->cc_email;

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $mailer = $this->getServiceLocator()->get('mail.transport');
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        $params = array(
            'year' => $study->getCurrentYear(),
            'studyUrl' => $renderer->serverUrl('/'),
            'studyName' => $study->getName(),
            'resetUrl' => $renderer->serverUrl('/reset-password'),
            'contactUrl' => $renderer->serverUrl('/contact')
        );

        $message = new Message();
        $message->setSubject("Welcome to " . $study->getName());
        $message->setFrom($from_email);
        $message->addBcc($cc_email);

        // Add recipients
        $users = $subscription->getCollege()->getUsersByStudy($study);
        foreach ($users as $user) {
            $message->setTo($user->getEmail());

            // Plug in the user's name
            $params['fullName'] = $user->getPrefix() . ' ' . $user->getLastName();
            $content = $renderer->render('mrss/email/welcome', $params);

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
    }

    public function deleteAction()
    {
        // Load the subscription
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        $subscriptionId = $this->params()->fromRoute('id');

        if ($subscriptionId == 'all') {
            takeYourTime();

            /** @var \Mrss\Entity\Study $study */
            $study = $this->currentStudy();
            if ($year = $this->params()->fromQuery('year')) {
                $subscriptions = $study->getSubscriptionsForYear($year);
            } else {
                $subscriptions = $study->getSubscriptions();
            }

            $message = '';
            foreach ($subscriptions as $subscription) {
                $message .= $this->deleteSubscription($subscription) . "<br>\n";
            }

            $this->flashMessenger()->addSuccessMessage($message);
            return $this->redirect()->toUrl('/admin');

        } else {
            $subscription = $subscriptionModel->find($subscriptionId);
        }


        // If the subscription's not found, redirect them
        if (empty($subscription)) {
            $this->flashMessenger()->addErrorMessage(
                'Unable to find subscription with id: ' . $subscriptionId
            );

            return $this->redirect()->toUrl('/admin');
        }

        $message = $this->deleteSubscription($subscription);


        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirect()->toUrl('/admin');
    }

    protected function deleteSubscription(Subscription $subscription)
    {
        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        // Load the subscription
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        $message = '';
        // See if this is the college's last subscription
        $college = $subscription->getCollege();
        if (count($college->getSubscriptions()) == 1) {
            // This college only has one subscription: the one we're deleting.
            // Delete the college and its users and observations
            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');

            /** @var \Mrss\Model\User $userModel */
            $userModel = $this->getServiceLocator()->get('model.user');

            // Delete users
            foreach ($college->getUsers() as $user) {
                $userModel->delete($user);
            }

            // Delete observations
            foreach ($college->getObservations() as $observation) {
                $observationModel->delete($observation);
            }

            // Delete college
            $collegeModel->delete($college);

            $message .= "Since {$college->getName()} only has this one subscription,
            the college and its users have been deleted. ";
        } else {
            $observation = $subscription->getObservation();

            if ($observation) {
                // Should we delete this year's observation
                $subscriptions = $observation->getSubscriptions();
                if (count($subscriptions) == 1) {
                    // This is the only subscription using the observation, so axe it
                    $observationModel->delete($observation);
                    $message .= "Observation deleted. ";
                } else {
                    // This college has other subscriptions. Don't delete the users or obs
                    // But do clear out their data for fields not in other studies
                    $benchmarkKeysToNull = $this->getBenchmarkKeysInThisStudyOnly(
                        $observation
                    );

                    foreach ($benchmarkKeysToNull as $dbColumn) {
                        $observation->set($dbColumn, null);
                    }

                    $observationModel->save($observation);

                    $count = count($benchmarkKeysToNull);
                    $message .= "$count fields cleared out from this year's observation. ";

                }
            }
        }

        // Delete the subscription row
        $subscriptionModel->delete($subscription);
        $subscriptionModel->getEntityManager()->flush();

        $message .= "Subscription deleted. ";

        return $message;
    }

    protected function getBenchmarkKeysInThisStudyOnly(Observation $observation)
    {
        /** @var \Mrss\Entity\Study $currentStudy */
        $currentStudy = $this->getStudy();

        $subscriptions = $observation->getSubscriptions();
        $allStudies = array();
        foreach ($subscriptions as $subscription) {
            $allStudies[] = $subscription->getStudy();
        }

        $benchmarksInCurrentStudy = $currentStudy->getAllBenchmarkKeys();

        // Temp fix. This will clear out some Max data, but it should get re-imported
        return $benchmarksInCurrentStudy;

        $benchmarksInCurrentStudyOnly = $benchmarksInCurrentStudy;

        foreach ($allStudies as $study) {
            if ($study->getId() == $currentStudy->getId()) {
                continue;
            }

            $benchmarksInCurrentStudyOnly = array_diff(
                $benchmarksInCurrentStudyOnly,
                $study->getAllBenchmarkKeys()
            );
        }

        return $benchmarksInCurrentStudyOnly;
    }

    /**
     * @return array|\GoalioForgotPassword\Service\Password
     */
    public function getPasswordService()
    {
        if (!$this->passwordService) {
            $this->passwordService = $this->getServiceLocator()
                ->get('goalioforgotpassword_password_service');
        }
        return $this->passwordService;
    }

    /**
     * @return Logger
     */
    public function getLog()
    {
        if (empty($this->log)) {
            $filename = 'postback.log';
            $logger = new Logger;
            $writer = new Stream($filename);
            $logger->addWriter($writer);

            $this->log = $logger;
        }

        return $this->log;
    }

    public function saveDraftSubscription($data)
    {
        $draft = new SubscriptionDraft();
        $draft->setFormData(json_encode($data));
        $draft->setDate(new DateTime('now'));
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $draft->setIp($ip);

        $this->getSubscriptionDraftModel()->save($draft);
        $this->getSubscriptionDraftModel()->getEntityManager()->flush();

        $this->saveTransIdToSession($draft->getId());

        // Save college id to session
        $this->getSessionContainer()->ipeds = $this->getIpeds($data);
    }

    public function setIpeds($ipeds)
    {
        $this->ipeds = $ipeds;

        return $this;
    }

    public function getIpeds($data = null)
    {
        if (empty($this->ipeds)) {
            $this->getLog()->info("Ipeds property not set. Generating it.");

            if (!empty($this->getSessionContainer()->ipeds)) {
                $ipeds = $this->getSessionContainer()->ipeds;
            } elseif (!empty($data['institution']['ipeds'])) {
                $ipeds = $data['institution']['ipeds'];
            } elseif ($college = $this->currentCollege()) {
                $ipeds = $college->getIpeds();
            } elseif ($draft = $this->getDraftSubscription()) {

                $ipeds = $draft->getIpeds();

                if (empty($ipeds)) {
                    $this->getLog()->info("Didn't find ipeds in draft. Must be a renewal.");

                    if ($collegeId = $draft->getCollegeId()) {
                        $this->getLog()->info("About to fetch ipeds from college: $collegeId.");

                        /** @var \Mrss\Model\College $collegeModel */
                        $collegeModel = $this->getServiceLocator()->get('model.college');
                        $college = $collegeModel->find($collegeId);

                        $ipeds = $college->getIpeds();
                    }
                }
            }

            if (empty($ipeds)) {
                throw new \Exception('Cannot save draft subscription without ipeds.');
            }

            $this->ipeds = $ipeds;
        }

        return $this->ipeds;
    }

    public function setDraftSubscription(SubscriptionDraft $draft)
    {
        $this->draftSubscription = $draft;
    }

    /**
     * @param null $id
     * @return null|\Mrss\Entity\SubscriptionDraft
     */
    public function getDraftSubscription($id = null)
    {
        if (empty($this->draftSubscription)) {
            if (empty($id)) {
                $id = $this->getTransIdFromSession();
            }

            $this->draftSubscription = $this->getSubscriptionDraftModel()->find($id);
        }

        return $this->draftSubscription;
    }

    public function saveAgreementToDraftSubscription($agreement)
    {
        $draft = $this->getDraftSubscription();
        $draft->setAgreementData(json_encode($agreement));
        $this->getSubscriptionDraftModel()->save($draft);
        $this->getSubscriptionDraftModel()->getEntityManager()->flush();
    }

    public function setSubscriptionDraftModel($model)
    {
        $this->subscriptionDraftModel = $model;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        return $subscriptionModel;
    }

    /**
     * @return \Mrss\Model\SubscriptionDraft
     */
    public function getSubscriptionDraftModel()
    {
        if (empty($this->subscriptionDraftModel)) {
            $this->subscriptionDraftModel = $this->getServiceLocator()
                ->get('model.subscriptionDraft');
        }

        return $this->subscriptionDraftModel;
    }

    public function downloadAction()
    {
        $model = $this->getSubscriptionModel();
        $study = $this->currentStudy();
        $year = $this->params()->fromRoute('year');

        $subscriptions = $model->findByStudyAndYear($study->getId(), $year);
        $c = count($subscriptions);

        $subscriptionsInfo[] = array(
            'Institution',
            'State',
            'IPEDS',
            'Campus Type',
            'Calendar',
            'Campus Environment',
            'Faculty Unionized',
            'Staff Unionized',
            'Control'
        );
        foreach ($subscriptions as $sub) {
            $college = $sub->getCollege();
            $observation = $sub->getObservation();

            $subscriptionsInfo[] = array(
                $college->getName(),
                $college->getState(),
                $college->getIpeds(),
                $observation->get('institutional_type'),
                $observation->get('institutional_demographics_calendar'),
                $observation->get('institutional_demographics_campus_environment'),
                $observation->get('institutional_demographics_faculty_unionized'),
                $observation->get('institutional_demographics_staff_unionized'),
                $observation->get('institutional_control'),
            );
        }

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->fromArray($subscriptionsInfo);

        foreach (range(0, count($subscriptionsInfo[0])) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // Make the first row bold
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $filename = $this->currentStudy()->getName() . '-Members-' . $year;

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
}
