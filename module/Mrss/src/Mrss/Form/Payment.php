<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;

class Payment extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('payment');

        // Add elements


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
