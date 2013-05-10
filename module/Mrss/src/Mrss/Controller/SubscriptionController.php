<?php

namespace Mrss\Controller;

use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Form\Payment;
use Mrss\Form\SubscriptionInvoice;
use Mrss\Form\SubscriptionSystem;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Session\Container;
use Mrss\Form\Subscription as SubscriptionForm;
use Mrss\Form\Fieldset\Agreement;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Class SubscriptionController
 *
 * @todo: Make this handle subscribing to any study
 *
 * @package Mrss\Controller
 */
class SubscriptionController extends AbstractActionController
{
    protected $sessionContainer;

    public function addAction()
    {
        $form = new SubscriptionForm;
        $message = null;

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                // If the form is valid, stash it in the session and go to
                // the agreement page
                $this->saveSubscriptionToSession($form->getData());

                $this->redirect()->toRoute('subscribe/user-agreement');
            } else {
                $message = "Please correct the problems below.";
            }
        }

        return array(
            'form' => $form,
            'message' => $message
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

        $message = null;

        $form = new Form('agreement');
        $fieldset = new Agreement();
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

                // Once they've agreed to the terms, redirect to the payment page
                $this->redirect()->toRoute('subscribe/payment');
            } else {
                $message = "Please correct the problems below.";
            }
        }

        return array(
            'form' => $form,
            'message' => $message,
            'subscription' => $this->getSubscriptionFromSession()
        );
    }

    public function paymentAction()
    {
        $this->checkSubscriptionIsInProgress();

        $ccForm = new Payment();

        $invoiceForm = new SubscriptionInvoice();
        $invoiceForm->setAttribute('action', '/subscribe/invoice');

        $systemForm = new SubscriptionSystem();
        //$systemForm->setAttribute('action', '/subscribe/complete');



        return array(
            'ccForm' => $ccForm,
            'invoiceForm' => $invoiceForm,
            'systemForm' => $systemForm
        );
    }

    public function completeAction()
    {

    }

    public function invoiceAction()
    {
        $this->checkSubscriptionIsInProgress();

        $invoiceForm = new SubscriptionInvoice();

        // Handle form submissions
        if ($this->getRequest()->isPost()) {
            $invoiceForm->setData($this->params()->fromPost());

            if ($invoiceForm->isValid()) {
                $this->completeSubscription(
                    $this->getSessionContainer()->subscribeForm,
                    $invoiceForm->getData()
                );

                // @todo: send invoice email like NCCCPP

                $this->flashMessenger()->addSuccessMessage(
                    "Thank you for subscribing. "
                );

                $this->redirect()->toUrl('/');
            }
        }
    }

    public function checkSubscriptionIsInProgress()
    {
        if (!$sub = $this->getSubscriptionFromSession()) {
            throw new \Exception('Subscription not present in session.');
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
     */
    public function completeSubscription($subscriptionForm, $paymentForm)
    {
        // Create or fetch the college
        $institutionForm = $subscriptionForm['institution'];
        $college = $this->createOrUpdateCollege($institutionForm);

        // Create the observation
        $observation = $this->createOrUpdateObservation($college);

        // Create the subscription record with payment info
        $this->createOrUpdateSubscription($paymentForm, $college, $observation);

        // Create the users, if needed
        // @todo: set different roles
        
        // Admin first
        $adminContactForm = $subscriptionForm['adminContact'];
        $this->createOrUpdateUser($adminContactForm, 'user', $college);

        // Now data user
        $dataContactForm = $subscriptionForm['dataContact'];
        $this->createOrUpdateUser($dataContactForm, 'user', $college);

        // Save it all to the db
        $this->getServiceLocator()->get('em')->flush();
    }

    protected function getCurrentYear()
    {
        return 2013;
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
        $user->setPassword('test');
        
        // @todo: set role
        

        $userModel->save($user);

        if (!empty($createUser)) {
            // @todo: Send out email with one-time login link
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

        // Get the agreement data from the session
        $agreement = $this->getAgreementFromSession();

        $subscription->setYear($this->getCurrentYear());
        $subscription->setStatus('complete');
        $subscription->setCollege($college);
        $subscription->setStudy($this->getStudy());
        $subscription->setPaymentMethod($method);
        $subscription->setObservation($observation);
        $subscription->setDigitalSignature($agreement['signature']);
        $subscription->setDigitalSignatureTitle($agreement['title']);

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
        // @todo: make this dynamic
        $studyId = 1;

        /** @var \Mrss\Model\Study $studyModel */
        $studyModel = $this->getServiceLocator()->get('model.study');
        $study = $studyModel->find($studyId);

        if (empty($study)) {
            throw new \Exception("Study with id $studyId not found.");
        }

        return $study;
    }
}
