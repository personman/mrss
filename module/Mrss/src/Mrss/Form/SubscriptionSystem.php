<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;

class SubscriptionSystem extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('system');

        // Add elements
        $this->add(
            array(
                'name' => 'system',
                'type' => 'Text',
                'options' => array(
                    'label' => 'College System or State System'
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
                    'value' => 'Paid by System'
                )
            )
        );

        return $fieldset;
    }
}
