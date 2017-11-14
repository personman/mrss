<?php

namespace Mrss\Form;

use Zend\Form\Fieldset;
use Zend\Validator;

class SubscriptionInvoice extends AbstractForm
{
    public function __construct($name = 'subscriptionInvoice')
    {
        // Call the parent constructor
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->addPaymentType('invoice');

        // Submit button
        $this->add(
            $this->getSubmitFieldset()
        );
    }

    protected function addPaymentType($value = 'invoice')
    {
        $this->add(
            array(
                'name' => 'paymentType',
                'type' => 'Hidden',
                'attributes' => array(
                    'value' => $value
                )
            )
        );
    }

    protected function getSubmitFieldset($label = 'Request an Invoice')
    {
        $fieldset = new Fieldset('submit');

        $fieldset->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'btn btn-primary',
                    'value' => $label
                )
            )
        );

        return $fieldset;
    }
}
