<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Benchmark extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmark');

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

        // @todo: Only show this to sr admins.
        $this->add(
            array(
                'name' => 'dbColumn',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Database Column'
                )
            )
        );

        $this->add(
            array(
                'name' => 'inputType',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Input Type'
                ),
                'attributes' => array(
                    'options' => array(
                        'number' => 'Number',
                        'percent' => 'Percent',
                        'text' => 'Text',
                        'computed' => 'Computed'
                    )
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }
}
