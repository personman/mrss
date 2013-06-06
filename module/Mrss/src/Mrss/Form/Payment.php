<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;

/**
 * Class Payment
 *
 * Credit card payment
 *
 * @package Mrss\Form
 */
class Payment extends AbstractForm
{
    public function __construct($uPaySiteId, $uPayUrl, $amount)
    {
        if (empty($uPaySiteId)) {
            throw new \Exception("Payment form requires uPaySiteId");
        }
        if (empty($uPayUrl)) {
            throw new \Exception("Payment form requires uPayUrl");
        }

        // Call the parent constructor
        parent::__construct('payment');

        $this->setAttribute('action', $uPayUrl);
        $this->setAttribute('method', 'post');

            // Add elements
        $this->add(
            array(
                'name' => 'UPAY_SITE_ID',
                'type' => 'Hidden',
                'attributes' => array(
                    'value' => $uPaySiteId
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
        //$fieldset->setAttribute('class', 'well');

        $fieldset->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'btn btn-primary',
                    'value' => 'Pay by Credit Card'
                )
            )
        );

        return $fieldset;
    }
}
