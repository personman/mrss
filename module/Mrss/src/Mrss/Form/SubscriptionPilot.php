<?php

namespace Mrss\Form;

use Zend\Validator;

class SubscriptionPilot extends SubscriptionInvoice
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscriptionPilot');

        $this->setAttribute('method', 'post');
        $this->addPaymentType('pilot');

        // Submit button
        $this->add(
            $this->getSubmitFieldset('Free')
        );
    }
}
