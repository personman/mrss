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

        $fieldset = new Fieldset('buttons');
        $fieldset->setAttribute('class', 'well well-small');

        $fieldset->add(
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

        $fieldset->add(
            array(
                'name' => 'url',
                'type' => 'Text',
                'options' => array(
                    'label' => 'URL'
                ),
                'attributes' => array(
                    'id' => 'url'
                )
            )
        );

        $fieldset->add(
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



        $this->addButtons($fieldset);

        $this->add($fieldset);
    }


    protected function addButtons($fieldset)
    {
        // Fieldset for buttons
        $buttons = new Fieldset('buttons');

        $save = new Element\Submit('submit');
        $save->setValue('Save');
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

        $fieldset->add($buttons);
    }
}
