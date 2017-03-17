<?php

namespace Mrss\Form;

use Zend\Form\Element;

class BenchmarkSelect extends AbstractForm
{
    public function __construct($benchmarks)
    {
        // Call the parent constructor
        parent::__construct('benchmark_select');

        $this->addBenchmarkSelect($benchmarks);
    }

    protected function addBenchmarkSelect($benchmarks)
    {
        $this->add(
            array(
                'name' => 'benchmark',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Measure'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'benchmark'
                )
            )
        );
    }
}
