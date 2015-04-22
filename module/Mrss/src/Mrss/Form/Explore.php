<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class Explore extends AbstractForm
{

    public function __construct($benchmarks, $colleges)
    {
        // Call the parent constructor
        parent::__construct('explore');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'title',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Title'
                )
            )
        );

        $this->add(
            array(
                'name' => 'presentation',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Presentation Type'
                ),
                'attributes' => array(
                    'id' => 'inputType',
                    'options' => array(
                        'scatter' => 'Scatter Plot',
                        'bubble' => 'Bubble Plot',
                    )
                )
            )
        );


        $this->add(
            array(
                'name' => 'benchmark1',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'X Axis'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'benchmark1'
                )
            )
        );

        $this->add(
            array(
                'name' => 'benchmark2',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Y Axis'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'benchmark2'
                )
            )
        );

        $this->add(
            array(
                'name' => 'benchmark3',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Bubble Size'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'benchmark3'
                )
            )
        );

        /*$this->add(
            array(
                'name' => 'highlightedCollege',
                'type' => 'Select',
                'options' => array(
                    'label' => 'College'
                ),
                'attributes' => array(
                    'options' => $colleges
                )
            )
        );*/

        $this->add(
            array(
                'name' => 'content',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Description'
                ),
                'attributes' => array(
                    'rows' => 4,
                )
            )
        );



        $this->add($this->getButtonFieldset('Go'));
    }
}
