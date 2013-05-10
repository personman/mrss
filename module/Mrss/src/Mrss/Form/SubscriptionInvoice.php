<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Validator;

class SubscriptionInvoice extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscriptionInvoice');

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'paymentType',
                'type' => 'Hidden',
                'attributes' => array(
                    'value' => 'invoice'
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
                    'value' => 'Request an Invoice'
                )
            )
        );

        return $fieldset;
    }
}
