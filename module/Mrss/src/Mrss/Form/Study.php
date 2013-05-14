<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Study extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('study');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'description',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Description'
                ),
                'attributes' => array(
                    'rows' => 8
                )
            )
        );

        $this->add(
            array(
                'name' => 'currentYear',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Current Year'
                ),
                'attributes' => array(
                    'options' => $this->getYearsAvailable()
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }
}
