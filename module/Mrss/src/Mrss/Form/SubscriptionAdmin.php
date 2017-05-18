<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Validator;
use Zend\Filter;
use Zend\Form\Element;
use Mrss\Form\Fieldset\User as UserFieldset;
use Mrss\Form\Fieldset\College;
use Mrss\Form\Fieldset\Executive;

class SubscriptionAdmin extends AbstractForm
{
    public function __construct($systems = array(), $systemLabel)
    {
        $currentYear = date('Y');
        $years = range($currentYear - 10, $currentYear + 5);
        $years = array_combine($years, $years);

        // Call the parent constructor
        parent::__construct('subscription');

        $this->add(
            array(
                'name' => 'college',
                'type' => 'Hidden',
            )
        );

        $this->add(
            array(
                'name' => 'year',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Year',
                    'value' => $currentYear
                ),
                'attributes' => array(
                    'id' => 'year',
                    'options' => $years,

                )
            )
        );

        if (count($systems)) {
            $this->add(
                array(
                    'name' => 'systems',
                    'type' => 'Zend\Form\Element\MultiCheckbox',
                    'options' => array(
                        'label' => ucwords($systemLabel) . 's',
                    ),
                    'attributes' => array(
                        'id' => 'year',
                        'options' => $systems,

                    )
                )
            );
        }

        $this->add($this->getButtonFieldset());
    }
}
