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

        $this->add($this->getButtonFieldset());
    }
}
