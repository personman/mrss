<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Mrss\Form\Subscription as SubscriptionForm;
use Mrss\Form\Fieldset\Agreement;


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
                var_dump($form->getData());
                die('is valid');
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
                var_dump($form->getData());
                die('is valid');
            } else {
                $message = "Please correct the problems below.";
            }
        }

        return array(
            'form' => $form,
            'message' => $message
        );
    }

    public function paymentAction()
    {

    }
}
