<?php

namespace Mrss\Controller;

use Mrss\Form\Payment;
use Mrss\Form\SubscriptionInvoice;
use Mrss\Form\SubscriptionSystem;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Session\Container;
use Mrss\Form\Subscription as SubscriptionForm;
use Mrss\Form\Fieldset\Agreement;

/**
 * Class SubscriptionController
 *
 * @todo: Make this handle subscribing to any study
 *
 * @package Mrss\Controller
 */
class SubscriptionController extends AbstractActionController
{
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
        // @todo: Confirm that the session data is present

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
        // @todo: Confirm that the session data is present

        $ccForm = new Payment();
        $invoiceForm = new SubscriptionInvoice();
        $systemForm = new SubscriptionSystem();

        return array(
            'ccForm' => $ccForm,
            'invoiceForm' => $invoiceForm,
            'systemForm' => $systemForm
        );
    }

    public function saveSubscriptionToSession($subscriptionForm)
    {
        $container = new Container('subscribe');
        $container->subscribeForm = $subscriptionForm;

    }

    public function getSubscriptionFromSession()
    {
        $container = new Container('subscribe');

        return $container->subscribeForm;
    }
}
