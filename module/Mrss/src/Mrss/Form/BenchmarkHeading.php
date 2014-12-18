<?php

namespace Mrss\Form;

class BenchmarkHeading extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmarkHeading');

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
                    'label' => 'Label'
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

        $this->add($this->getButtonFieldset('Save', false, true));
    }
}
