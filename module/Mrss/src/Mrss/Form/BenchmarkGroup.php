<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class BenchmarkGroup extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmarkGroup');

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
                'name' => 'shortName',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Short Name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'format',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Format'
                ),
                'attributes' => array(
                    'options' => array(
                        'one-col' => 'One column',
                        'two-col' => 'Two columns'
                    )
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
                'name' => 'useSubObservation',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Use SubObservation',
                    'help-block' => 'Replaces institution-wide data entry form with
                    a division/unit level form.'
                ),
            )
        );

        $this->add($this->getButtonFieldset());
    }
}
