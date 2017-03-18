<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class BenchmarkSelect extends AbstractForm
{
    public function __construct($benchmarks)
    {
        // Call the parent constructor
        parent::__construct('benchmark_select');

        $this->addHeading();
        $this->addBenchmarkSelect($benchmarks);
        $this->addButtons();
    }

    protected function addHeading()
    {
        $this->add(
            array(
                'name' => 'heading',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Heading'
                ),
                'attributes' => array(
                    'id' => 'heading'
                )
            )
        );
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

    protected function addButtons()
    {
        // Fieldset for buttons
        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');

        $save = new Element\Submit('submit');
        $save->setValue('Add');
        $save->setAttribute('class', 'btn btn-primary');
        $save->setAttribute('id', 'submitButton');
        $buttons->add($save);

        // Add the reset button
        $reset = new Element\Submit('cancel');
        $reset->setValue('Cancel');
        //$reset->setLabel('Cancel');
        $reset->setAttribute('class', 'btn btn-danger');
        $reset->setAttribute('id', 'cancelButton');
        $buttons->add($reset);

        $this->add($buttons);
    }
}
