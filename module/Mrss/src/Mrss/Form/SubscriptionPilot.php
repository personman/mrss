<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Validator;

class SubscriptionPilot extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscriptionPilot');

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'paymentType',
                'type' => 'Hidden',
                'attributes' => array(
                    'value' => 'pilot'
                )
            )
        );


        // Submit button
        $this->add(
            $this->getSubmitFieldset()
        );
    }

    public function getSubmitFieldset()
    {
        $fieldset = new Fieldset('submit');

        $fieldset->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'btn btn-primary',
                    'value' => 'Free'
                )
            )
        );

        return $fieldset;
    }
}
