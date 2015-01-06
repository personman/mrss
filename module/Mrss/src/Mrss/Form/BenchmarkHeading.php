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
                    'label' => 'Label',
                    'help-block' => 'For dynamic years, use [year], [year_minus_2], etc.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'description',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Description',
                    'help-block' => 'For dynamic years, use [year], [year_minus_2], etc.'
                ),
                'attributes' => array(
                    'rows' => 8
                )
            )
        );

        $this->add($this->getButtonFieldset('Save', false, true));
    }
}
