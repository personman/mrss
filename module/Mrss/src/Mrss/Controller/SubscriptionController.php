<?php

namespace Mrss\Controller;

use Mrss\Entity\Section;
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
use Mrss\Form\SubscriptionModule;
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
use Zend\View\Model\ViewModel;

/**
 * Class SubscriptionController
 *
 * @package Mrss\Controller
 */
class SubscriptionController extends SubscriptionBaseController
{
    protected $draftSubscription;

    protected $ipeds;

    protected $oldTotal = 0;

    /**
     * A regular user can view his or her subscription details. Redirect here after
     * subscribing
     */
    public function viewAction()
    {
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $study = $this->currentStudy();

        $collegeId = null;
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
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

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
            //die('sent');
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

                if ($this->getStudy()->hasSections()) {
                    return $this->redirect()->toRoute('subscribe/modules');
                } else {
                    return $this->redirect()->toRoute('subscribe/user-agreement');
                }
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
        $form = new SubscriptionFree();

        $formHasErrors = 0;

        // If the form is submitted, they need to create a new subscription and new user
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $collegeId = $data['id'];
                $college = $this->getCollegeModel()->find($collegeId);

                if (!empty($college)) {
                    $data = array(
                        'renew' => false,
                        'free' => true,
                        'college_id' => $college->getId(),
                        'user' => $data['user']
                    );

                    $this->saveDraftSubscription($data);

                    if ($this->getStudy()->hasSections()) {
                        return $this->redirect()->toRoute('subscribe/modules');
                    } else {
                        return $this->redirect()->toRoute('subscribe/user-agreement');
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage("Unable to find institution.");
                    return $this->redirect()->toUrl('/participate');
                }
            } else {
                $formHasErrors = 1;
            }
        }

        return array(
            'form' => $form,
            //'allColleges' => $this->getAllColleges(),
            'formHasErrors' => $formHasErrors
        );
    }

    protected function joinFreeFinal()
    {
        $draft = $this->getDraftSubscription();
        $data = json_decode($draft->getFormData(), true);
        $collegeId = $draft->getCollegeId();
        $college = $this->getCollegeModel()->find($collegeId);

        // Create the observation
        $observation = $this->createOrUpdateObservation($college);

        $subscription = $this->createOrUpdateSubscription(
            array('paymentType' => 'free'),
            $college,
            $observation
        );

        // create the user.
        if (!empty($data['user'])) {
            // Set state to 0
            $defaultRole = 'data';
            $userData = $data['user'];
            $defaultState = 0;

            $user = $this->createOrUpdateUser($userData, $defaultRole, $college, $defaultState);
            //unset($user);
        }

        $this->getEntityManager()->flush();

        // redirect
        return $this->redirect()->toRoute('joined');
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

    public function renewAction()
    {
        $college = $this->currentCollege();
        $study = $this->currentStudy();


        // Make sure it's open
        if (!$study->getEnrollmentOpen()) {
            $this->flashMessenger()->addErrorMessage('Enrollment is not currently open. Please check back later.');
            return $this->redirect()->toUrl('/');
        }


        $form = new AbstractForm('renew');

        $button = 'Renew';
        if ($this->getStudyConfig()->free_to_join) {
            $button = 'Continue';
        }

        $form->add($form->getButtonFieldset($button));

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = array(
                    'renew' => true,
                    'college_id' => $this->currentCollege()->getId()
                );

                $this->saveDraftSubscription($data);

                if ($this->getStudy()->hasSections()) {
                    return $this->redirect()->toRoute('subscribe/modules');
                } else {
                    return $this->redirect()->toRoute('subscribe/user-agreement');
                }
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

                if ($this->getStudyConfig()->free_to_join) {
                    $draft = $this->getDraftSubscription();
                    $data = $draft->getFormData();

                    if (!empty($data['renew'])) {
                        // Skip the payment page and complete the free renewal
                        return $this->redirect()->toRoute('subscribe/free');
                    } else {
                        // Complete the free join
                        return $this->joinFreeFinal();
                    }
                } else {
                    // Once they've agreed to the terms, redirect to the payment page
                    return $this->redirect()->toRoute('subscribe/payment');
                }
            } else {
                $this->flashMessenger()->addErrorMessage(
                    "Please correct the problems below."
                );
            }
        }

        $viewModel = new ViewModel(
            array(
                'form' => $form,
                'subscription' => $this->getDraftSubscription()->getFormData(),
                'isRenewal' => $this->isRenewal(),
                'paymentAmount' => $this->getPaymentAmount()
            )
        );

        // Set the template
        $template = 'mrss/subscription/agreement';
        if ($configTemplate = $this->getStudyConfig()->agreement_template) {
            $template = 'mrss/subscription/' . $configTemplate;
        }

        return $viewModel->setTemplate($template);
    }

    public function modulesAction()
    {
        $study = $this->getStudy();
        $sections = array();
        foreach ($study->getSections() as $section) {
            $name = $section->getName();
            if ($desc = $section->getDescription()) {
                $name .= " - " . $desc;
            }

            $sections[$section->getId()] = $name;
        }

        $form = new SubscriptionModule($sections);

        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $this->saveDraftSections($data['sections']);

                return $this->redirect()->toRoute('subscribe/user-agreement');
            }
        }

        return array(
            'form' => $form
        );
    }

    public function editAction()
    {
        $subscription = $this->getSubscriptionModel()
            ->findCurrentSubscription($this->getStudy(), $this->currentCollege());

        if (empty($subscription)) {
            return $this->redirect()->toUrl('/join');
        }

        if ($subscription->hasAllSections()) {
            $this->flashMessenger()->addSuccessMessage('Your membership already includes all available modules.');
            return $this->redirect()->toUrl('/');
        }

        $study = $this->getStudy();

        $selectedSections = array();
        foreach ($subscription->getSections() as $section) {
            $selectedSections[] = $section->getId();
        }

        $sections = array();
        foreach ($study->getSections() as $section) {
            $selected = in_array($section->getId(), $selectedSections);
            $sections[] = array(
                'value' => $section->getId(),
                'label' => $section->getName(),
                'selected' => $selected,
                'disabled' => $selected,
            );
        }



        $form = new SubscriptionModule($sections);

        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $selectedSections = $data['sections'];

                // Merge in the previously selected sections
                $selectedSections = array_merge($selectedSections, $subscription->getSectionIds());

                // Save to session and
                $this->createDraftSubscriptionForUpdate($subscription, $selectedSections);

                //$this->saveDraftSections($data['sections']);

                return $this->redirect()->toRoute('subscribe/payment');
            }
        }

        return array(
            'form' => $form,
        );
    }

    public function adminEditAction()
    {
        $subscriptionId = $this->params()->fromRoute('id');
        $subscription = $this->getSubscriptionModel()->find($subscriptionId);
        $entityManager = $this->getSubscriptionModel()->getEntityManager();

        $selectedSystems = $subscription->getCollege()->getSystemsByYear($subscription->getYear());
        $systemIds = array();
        foreach ($selectedSystems as $sys) {
            $systemIds[] = $sys->getId();
        }

        $form = $this->getAdminForm($subscription->getCollege()->getId());

        if ($form->has('systems')) {
            $form->get('systems')->setValue($systemIds);
        }

        if ($form->has('modules')) {
            $moduleIds = $subscription->getSectionIds();
            $form->get('modules')->setValue($moduleIds);
        }



        $form->setHydrator(new DoctrineHydrator($entityManager, 'Mrss\Entity\Subscription'));
        $form->bind($subscription);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // Handle networks
                $this->updateSystemMemberships(
                    $subscription->getCollege(),
                    $this->params()->fromPost('systems'),
                    $subscription->getYear()
                );

                $modules = $this->params()->fromPost('modules');
                $this->updateSections(
                    $subscription,
                    $modules
                );

                $this->getSubscriptionModel()->save($subscription);

                $this->getSubscriptionModel()->getEntityManager()->flush();

                //prd($subscription->getSectionIds());

                $this->flashMessenger()->addSuccessMessage('Saved');
                return $this->redirect()
                    ->toRoute('colleges/view', array('id' => $subscription->getCollege()->getId()));
            }
        }

        return array(
            'form' => $form
        );
    }

    protected function createDraftSubscriptionForUpdate($subscription, $sections)
    {
        $draft = $this->getDraftSubscription();
        if (!$draft) {
            $draft = $this->saveDraftSubscription();
        }

        $draft->setSubscription($subscription);

        // This triggers a save and flush:
        $this->saveDraftSections($sections);

        return $draft;
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

        $isUpdate = $this->getDraftSubscription()->isUpdate();

        $isRenewal = false;
        if (!empty($sub['renew']) || $isUpdate) {
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
        $transId = $this->getTransIdFromSession();
        $val = $this->getEncodedValidationKey($amount, $transId);

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
            'pilotForm' => $pilotForm,
            'amount' => $amount
        );
    }

    protected function getEncodedValidationKey($amount, $transId)
    {
        // Calculate the validation key for uPay/TouchNet
        // @todo: put this in the db, too:
        $val = 'kdifvn3e9oskndfk';
        $validationKey = $val . $transId . $amount;
        $validationKey = md5($validationKey);
        $val = base64_encode(pack('H*', $validationKey));

        return $val;
    }

    public function getPaymentAmount()
    {
        $isRenewal = $this->isRenewal();

        $selectedSections = json_decode($this->getDraftSubscription()->getSections(), true);

        // Get this dynamically based on study, date, renewal, and selected modules
        $amount = $this->getStudy()->getCurrentPrice($isRenewal, $selectedSections);

        // Are we just updating the selected sections/modules? If so, get the difference between the
        // original subscription payment amount and the new amount. Return that, skipping offer codes and discounts
        if ($this->getDraftSubscription()->isUpdate()) {
            $originalPayment = $this->getDraftSubscription()->getSubscription()->getPaymentAmount();

            // Hard code for when they paid under early bird pricing originally and are adding a module after
            if ($originalPayment == 1250 && $amount == 1950) {
                $originalPayment = 1450;
            }

            $difference = $amount - $originalPayment;



            return $difference;
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
            $discount = $this->getNhebiDiscount();
            $amount = $amount - $discount;
        }

        return $amount;
    }

    protected function getNhebiDiscount()
    {
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
        $discount = $service->checkForDiscount($year, $ipeds);

        return $discount;
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

    /**
     * Cancel button clicked on TouchNet payment screen. Just redirect and show a message.
     */
    public function cancelAction()
    {
        $this->flashMessenger()
            ->addInfoMessage("Credit card payment canceled. Please try again or select another payment method");

        return $this->redirect()->toRoute('subscribe/payment');
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

    public function freeAction()
    {
        $this->checkSubscriptionIsInProgress();
        $this->checkEnrollmentIsOpen();

        return $this->completeSubscription(
            $this->getDraftSubscription(),
            array('paymentType' => 'free')
        );
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

    /**
     * @throws \Exception
     */
    public function checkSubscriptionIsInProgress()
    {
        if (!$sub = $this->getDraftSubscription()) {
            throw new \Exception(
                'You do not appear to have a subscription in progress. Please start
                over.'
            );
        }

        unset($sub);
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

    /**
     * @param SubscriptionDraft $subscriptionDraft
     * @return Section[]|array
     */
    protected function getSelectedSections($subscriptionDraft)
    {
        $sections = array();
        if (is_object($subscriptionDraft)) {
            $sectionIds = json_decode($subscriptionDraft->getSections(), true);

            $sections = $this->getSectionsByIds($sectionIds);
        }

        return $sections;
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
        if ($subscriptionDraft->isUpdate()) {
            return $this->updateSubscription($subscriptionDraft, $paymentForm, $sendInvoice, $redirect);
        }

        $subscriptionForm = json_decode($subscriptionDraft->getFormData(), true);

        // Create or fetch the college
        if (empty($subscriptionForm['renew'])) {
            $institutionForm = $subscriptionForm['institution'];
            $execForm = $subscriptionForm['executive'];
            $institutionForm = array_merge($institutionForm, $execForm);
            $college = $this->createOrUpdateCollege($institutionForm);
        } else {
            $collegeId = $subscriptionForm['college_id'];
            $college = $this->getCollegeModel()->find($collegeId);
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

        $this->clearDraftSubscription($subscriptionDraft);

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

    protected function clearDraftSubscription($subscriptionDraft)
    {
        // Now clear out the draft subscription
        $this->getSubscriptionDraftModel()->delete($subscriptionDraft);
        $this->getEntityManager()->flush();
    }

    /**
     * @param \Mrss\Entity\SubscriptionDraft $subscriptionDraft
     * @param $paymentForm
     * @param $sendInvoice
     * @param $redirect
     * @return \Zend\Http\Response
     */
    public function updateSubscription($subscriptionDraft, $paymentForm, $sendInvoice, $redirect)
    {
        $subscription = $subscriptionDraft->getSubscription();

        // Update the subscription sections
        $sections = $this->getSelectedSections($subscriptionDraft);
        $subscription->setSections($sections);

        // Update the payment amount (new total)
        $this->oldTotal = $oldTotal = $subscription->getPaymentAmount();
        $amountDue = $this->getPaymentAmount();
        $paymentAmount = $oldTotal + $amountDue;

        $subscription->setPaymentAmount($paymentAmount);

        $method = $paymentForm['paymentType'];
        $subscription->setPaymentMethod($method);

        // Send the invoice notification (if they want a paper invoice
        if ($sendInvoice) {
            $this->sendInvoice($subscription, null, null, null, true);
        }

        $date = date('c');
        $note = "Membership updated on $date. Payment amount changed from $oldTotal to $paymentAmount.";
        $subscription->addPaidNote($note);

        $this->getSubscriptionModel()->save($subscription);
        $this->getSubscriptionModel()->getEntityManager()->flush();


        $this->clearDraftSubscription($subscriptionDraft);

        // Send a notification to slack
        $this->sendSlackNotification($subscription, true);

        if ($redirect) {
            $message = "Your membership has been updated. You now have access to the selected modules. ";
            $this->flashMessenger()->addSuccessMessage($message);

            $redirect = $this->redirect()->toRoute('membership');
        }

        return $redirect;
    }

    protected function sendSlackNotification(Subscription $subscription, $update = false)
    {
        $college = $subscription->getCollege()->getName();
        $state = $subscription->getCollege()->getState();
        $college .= " ($state)";
        $appName = $this->currentStudy()->getName();
        if ($sectionNames = $subscription->getSectionNames()) {
            $appName .= " ($sectionNames)";
        }
        $cost = number_format($subscription->getPaymentAmount());
        $cost .= ' (' . $subscription->getPaymentMethodForDisplay() . ')';

        $message = "New $appName membership for $college. ";
        if ($update) {
            $message = "Updated $appName membership for $college. ";
        }

        $message .= "Cost: $$cost. ";

        // Add notes about subscription totals:
        $year = $subscription->getYear();

        list($count, $total) = $this->getSubscriptionCountAndTotal($year);
        $message .= " $count members, $$total. ";

        if ($channelSetup = $this->getSlackChannel()) {
            list($channel, $icon, $username) = $channelSetup;

            $this->slack($message, $channel, $icon, $username);
        }
    }

    protected function getSubscriptionCountAndTotal($year)
    {
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();

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

        return array($count, $total);
    }

    protected function getSlackChannel()
    {
        $studyId = $this->currentStudy()->getId();

        $channelSetup = null;

        // Configure channel, etc
        $map = array(
            1 => array('nccbp-website', ':nccbp:', 'NCCBP-bot'),
            2 => array('maximizing-website', ':maximizing:', 'Max-bot'),
            3 => array('workforce-website', ':workforce:', 'Workforce-bot')
        );

        if (array_key_exists($studyId, $map)) {
            $channelSetup = $map[$studyId];
        }

        return $channelSetup;
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
            $subscription->setCompletion(0);
        }

        if ($method == 'system') {
            $subscription->setPaymentSystemName($paymentForm['system']);
        }

        $status = $this->getSubscriptionStatus($method);

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

        // Modules/sections
        $selectedSections = $this->getSelectedSections($draftSubscription);

        //prd($selectedSections);
        $subscription->updateSections($selectedSections);


        $this->getSubscriptionModel()->save($subscription);
        $this->getSubscriptionModel()->getEntityManager()->flush();

        $this->createDataRows($subscription);

        return $subscription;
    }

    /**
     * @param Subscription $subscription
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createDataRows($subscription)
    {
        // First, make sure there aren't any
        $data = $subscription->getData();
        $subscriptionId = $subscription->getId();

        $count = count($data);

        if ($count == 0) {
            $sql = "INSERT INTO data_values (subscription_id, benchmark_id, dbColumn) ".
                "SELECT :subscription_id, id, dbColumn FROM benchmarks;";

            $stmt = $this->getSubscriptionModel()->getEntityManager()->getConnection()->prepare($sql);
            $stmt->execute(array('subscription_id' => $subscriptionId));
        }
    }

    protected function getSubscriptionStatus($method)
    {
        // Status: cc = complete, invoice or system = pending
        if ($method == 'creditCard') {
            $status = 'complete';
        } elseif ($method == 'system') {
            $status = 'pending';
        } elseif ($method == 'free') {
            $status = 'complete';
        } elseif ($method == 'pilot') {
            $status = 'pilot';
        } else {
            $status = 'pending';
        }

        return $status;
    }
    
    public function sendinvoiceAction()
    {
        $subscriptionId = $this->params()->fromPost('id');
        $toAddress = $this->params()->fromPost('to');

        if ($subscriptionId && $sub = $this->getSubscriptionModel()->find($subscriptionId)) {
            $this->sendInvoice($sub, null, null, $toAddress);
            $this->flashMessenger()->addSuccessMessage('Invoice sent to ' . $toAddress);
        } else {
            $this->flashMessenger()->addErrorMessage('No membership found for id: ' . $toAddress);
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
        $toEmail = null,
        $update = false
    ) {
        // Check config to see if emails are being suppressed (by Behat, probably)
        $config = $this->getServiceLocator()->get('config');
        if (!empty($config['suppressEmail'])) {
            return false;
        }

        $invoice = new Message();
        $invoice->setFrom('info@benchmarkinginstitute.org', 'NHEBI Staff');

        if ($toEmail) {
            $invoice->addTo($toEmail);
        } else {
            $invoice->addTo('dfergu15@jccc.edu');
            $invoice->addTo('michelletaylor@jccc.edu');
        }

        $paymentMethod = $subscription->getPaymentMethod();

        $invoice->setSubject(
            $this->getInvoiceSubject($subscription, $paymentMethod, $update)
        );

        $body = $this->getInvoiceBody($subscription, $adminUser, $dataUser, $update);
        $invoice->setBody($body);

        $this->getServiceLocator()->get('mail.transport')->send($invoice);
    }

    /**
     * @param Subscription $subscription
     * @param $paymentMethod
     * @param bool $update
     * @return string
     */
    protected function getInvoiceSubject($subscription, $paymentMethod, $update = false)
    {
        // Email subject
        if ($subscription->getPaymentMethod() == 'pilot') {
            $subjectIntro = 'Pilot';
        } elseif ($paymentMethod == 'invoice') {
            $subjectIntro = 'Invoice';
        } else {
            $subjectIntro = 'Membership';
        }

        $study = $subscription->getStudy();
        $studyName = $study->getName();
        $college = $subscription->getCollege();
        $collegeName = $college->getName();
        $year = $subscription->getYear();

        if ($update) {
            $subject  = "$subjectIntro: $collegeName updated their $year $studyName membership";
        } else {
            $subject = "$subjectIntro: $collegeName joined $studyName for $year";
        }

        return $subject;
    }

    /**
     * @param Subscription $subscription
     * @param User $adminUser
     * @param User $dataUser
     * @param bool $update
     * @return string
     */
    protected function getInvoiceBody($subscription, $adminUser, $dataUser, $update = false)
    {
        $date = date('Y-m-d');
        $study = $subscription->getStudy();
        $college = $subscription->getCollege();
        $year = $subscription->getYear();

        $amountDue = number_format($subscription->getPaymentAmount(), 2);

        $amounts = "Amount Due: $amountDue\n";
        if ($update) {
            $oldTotal = $this->oldTotal;
            $newTotal = $subscription->getPaymentAmount();
            $amountDue = ($newTotal - $oldTotal);

            $oldTotal = number_format($oldTotal, 2);
            $newTotal = number_format($newTotal, 2);
            $amountDue = number_format($amountDue, 2);

            $amounts = "Updated Total: $newTotal\n";
            $amounts .= "Previous Payment: $oldTotal\n";
            $amounts .= "Amount Due: $amountDue\n";
        }

        $body =
            "Study: {$study->getName()}<br>\n" .
            "Year: $year<br>\n" .
            "Institution: {$college->getName()}<br>\n" .
            $amounts .
            "Payment Method: {$subscription->getPaymentMethodForDisplay()}<br>\n" .
            "Date: $date<br>\n" .
            "Address: {$college->getAddress()} {$college->getAddress2()}<br>\n" .
            "City: {$college->getCity()}<br>\n" .
            "State: {$college->getState()}<br>\n" .
            "Zip: {$college->getZip()}<br>\n" .
            "Digital Signature: {$subscription->getDigitalSignature()}<br>\n" .
            "Title: {$subscription->getDigitalSignatureTitle()}<br>\n";

        if ($sections = $subscription->getSectionNames()) {
            $body .= "Module(s): " . $sections . "<br>\n";
        }

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

        return $body;
    }

    protected function sendWelcomeEmail(Subscription $subscription)
    {
        $studyConfig = $this->getServiceLocator()->get('study');
        $from_email = $studyConfig->from_email;
        $cc_email = $studyConfig->cc_email;

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $mailer = $this->getServiceLocator()->get('mail.transport');
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        $resetUrl = $renderer->serverUrl('/reset-password');

        $params = array(
            'year' => $study->getCurrentYear(),
            'studyUrl' => $renderer->serverUrl('/'),
            'studyName' => $study->getName(),
            'resetUrl' => $resetUrl,
            'contactUrl' => $renderer->serverUrl('/contact'),
            'oneTimeLogin' => $resetUrl
        );

        $message = new Message();
        $message->setSubject("Welcome to " . $study->getDescription());
        $message->setFrom($from_email);
        $message->addBcc($cc_email);

        // Add recipients
        $users = $subscription->getCollege()->getUsersByStudy($study);
        foreach ($users as $user) {
            $message->setTo($user->getEmail());

            $oneTimeLogin = $renderer->serverUrl(
                $renderer->url(
                    'zfcuser/resetpassword',
                    array(
                        'userId' => $user->getId(),
                        'token' => $this->getPasswordResetKey($user->getId())
                    )
                )
            );

            $params['oneTimeLogin'] = $oneTimeLogin;

            // Plug in the user's name
            $params['fullName'] = $user->getPrefix() . ' ' . $user->getLastName();

            $emailTemplate = 'mrss/email/welcome';
            if ($configTemplate = $this->getStudyConfig()->welcome_email) {
                $emailTemplate = 'mrss/email/' . $configTemplate;
            }

            $content = $renderer->render($emailTemplate, $params);


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

    public function saveDraftSubscription($data = null)
    {
        $draft = new SubscriptionDraft();
        $draft->setFormData(json_encode($data));

        $draft->setDate(new DateTime('now'));
        $ipAddress = $this->getRequest()->getServer('REMOTE_ADDR');
        $draft->setIp($ipAddress);

        $this->getSubscriptionDraftModel()->save($draft);
        $this->getSubscriptionDraftModel()->getEntityManager()->flush();

        $this->saveTransIdToSession($draft->getId());

        // Save college id to session
        $this->getSessionContainer()->ipeds = $this->getIpeds($data);

        $this->draftSubscription = $draft;

        return $draft;
    }

    public function saveDraftSections($sections)
    {
        $draft = $this->getDraftSubscription();
        $draft->setSections(json_encode($sections));

        $this->getSubscriptionDraftModel()->save($draft);
        $this->getSubscriptionDraftModel()->getEntityManager()->flush();
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

            if (!empty($data['institution']['ipeds'])) {
                $ipeds = $data['institution']['ipeds'];
            } elseif (!empty($this->getSessionContainer()->ipeds)) {
                $ipeds = $this->getSessionContainer()->ipeds;
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
     * @param null $identifier
     * @return null|\Mrss\Entity\SubscriptionDraft
     */
    public function getDraftSubscription($identifier = null)
    {
        if (empty($this->draftSubscription)) {
            if (empty($identifier)) {
                $identifier = $this->getTransIdFromSession();
            }

            if ($identifier) {
                $this->draftSubscription = $this->getSubscriptionDraftModel()->find($identifier);
            }

            if (empty($this->draftSubscription)) {
                $currentSubscription = $this->currentObservation()->getSubscription();
                if ($draft = $this->getSubscriptionDraftModel()->findOneBySubscription($currentSubscription)) {
                    $this->draftSubscription = $draft;
                }
            }
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
        $this->draftModel = $model;
    }
}
