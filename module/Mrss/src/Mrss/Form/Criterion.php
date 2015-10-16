<?php

namespace Mrss\Form;

use Zend\Form\Element;

class Criterion extends AbstractForm
{
    public function __construct($benchmarks)
    {
        // Call the parent constructor
        parent::__construct('criterion');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $benchmarks = array_merge(array('' => 'Select a benchmark...'), $benchmarks);
        $this->add(
            array(
                'name' => 'benchmark',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Benchmark'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'benchmark'
                )
            )
        );

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name'
                ),
                'attributes' => array(
                    'id' => 'name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'helpText',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Help Text'
                ),
                'attributes' => array(
                    'rows' => 3,
                    'id' => 'helpText'
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }
}
