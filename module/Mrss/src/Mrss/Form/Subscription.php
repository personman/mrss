<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Validator;
use Zend\Filter;
use Mrss\Form\Fieldset\User as UserFieldset;

class Subscription extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscription');

        $institution = new \Mrss\Form\Fieldset\College;
        $this->add($institution);

        // Administrative Contact
        $this->add(
            $this->getUserFieldset(
                'adminContact',
                'Administrative Contact'
            )
        );

        // Data Contact
        $this->add(
            $this->getUserFieldset(
                'dataContact',
                'Data Contact'
            )
        );

        // Submit button
        $this->add(
            $this->getSubmitFieldset()
        );
    }

    public function getUserFieldset($name, $label)
    {
        $fieldset = new UserFieldset($name);
        $fieldset->setLabel($label);

        return $fieldset;
    }

    public function getSubmitFieldset()
    {
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

        return $fieldset;
    }
}
