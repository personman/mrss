<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class AnalyzeEquation extends AbstractForm
{
    public function __construct($benchmarks, $colleges, $years)
    {
        // Call the parent constructor
        parent::__construct('analyze_equation');
        $this->addBenchmarkSelect($benchmarks);
        $this->addCollegeSelect($colleges);
        $this->addYearSelect($years);
        $this->add($this->getButtonFieldset('Continue'));
    }

    protected function addBenchmarkSelect($benchmarks)
    {
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
    }

    protected function addCollegeSelect($colleges)
    {
        $this->add(
            array(
                'name' => 'college',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Institution'
                ),
                'attributes' => array(
                    'options' => $colleges,
                    'id' => 'college'
                )
            )
        );
    }

    protected function addYearSelect($years)
    {
        $this->add(
            array(
                'name' => 'year',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Year'
                ),
                'attributes' => array(
                    'options' => array_combine($years, $years),
                    'id' => 'year'
                )
            )
        );
    }
}
