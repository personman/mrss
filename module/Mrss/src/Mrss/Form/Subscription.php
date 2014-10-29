<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Validator;
use Zend\Filter;
use Zend\Form\Element;
use Mrss\Form\Fieldset\User as UserFieldset;
use Mrss\Form\Fieldset\College;

class Subscription extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscription');

        $institution = new College;
        $this->add($institution);

        // Administrative Contact
        $this->add(
            $this->getAdminUserFieldset(
                'adminContact',
                'Administrative Contact'
            )
        );

        // Data Contact
        $this->add(
            $this->getDataUserFieldset(
                'dataContact',
                'Data Contact'
            )
        );

        $this->labelRequired();

        // Submit button
        $this->add(
            $this->getSubmitFieldset()
        );
    }

    public function getAdminUserFieldset($name, $label)
    {
        $fieldset = new UserFieldset($name);
        $fieldset->setLabel($label);

        return $fieldset;
    }

    public function getDataUserFieldset($name, $label)
    {
        $fieldset = new UserFieldset($name);
        $fieldset->setLabel($label);

        // Checkbox for making both users the same
        $same = new Element\Checkbox('same');
        $same->setLabel('Same as Administrative Contact');
        $same->setAttribute('id', 'same-as-admin');

        $fieldset->add($same, array('priority' => 1));

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

    public function labelRequired()
    {
        //$e = $this->get();
        //prd($this);
        //foreach ($this->g() as $element) {
        //    prd($element);
        //}
    }
}
