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
    public function __construct($uPaySiteId, $amount)
    {
        // Call the parent constructor
        parent::__construct('payment');

        // Production:
        //$upayUrl = 'https://secure.touchnet.com/C20110_upay/web/index.jsp';

        // Test:
        $upayUrl = 'https://secure.touchnet.com:8443/C20110test_upay/web/index.jsp';
        $this->setAttribute('action', $upayUrl);
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
