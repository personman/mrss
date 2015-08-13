<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class Explore extends AbstractForm
{

    public function __construct($benchmarks, $colleges, $years, $peerGroups)
    {
        // Call the parent constructor
        parent::__construct('explore');

        $years = array_combine($years, $years);

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
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
                        'line' => 'Trend Line',
                        'bar' => 'Percentile Bar Chart',
                        'text' => 'Text'
                    )
                )
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
                'name' => 'year',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Year'
                ),
                'attributes' => array(
                    'id' => 'years',
                    'options' => $years
                )
            )
        );

        $this->addBenchmarkSelects($benchmarks);

        $this->addPeerGroupDropdown($peerGroups);

        $this->add(
            array(
                'name' => 'regression',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => 'Show Regression Line'
                ),
                'attributes' => array(
                    'options' => $benchmarks,
                    'id' => 'regression'
                )
            )
        );

        $this->add(
            array(
                'name' => 'hideMine',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => 'Hide My Data'
                ),
                'attributes' => array(
                    'id' => 'hideMine'
                )
            )
        );

        $this->add(
            array(
                'name' => 'hideNational',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => 'Hide National Data'
                ),
                'attributes' => array(
                    'id' => 'hideNational'
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
                    'id' => 'text-content'
                )
            )
        );



        $this->add($this->getButtons());
    }

    protected function addBenchmarkSelects($benchmarks)
    {
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

    }

    protected function addPeerGroupDropdown($peerGroups)
    {
        $this->add(
            array(
                'name' => 'peerGroup',
                'type' => 'Zend\Form\Element\Select',
                'allow_empty' => true,
                'required' => false,
                'options' => array(
                    'label' => 'Peer Group',
                    'empty_option' => 'None'
                ),
                'attributes' => array(
                    'options' => $peerGroups,
                    'id' => 'peerGroup'
                )
            )
        );

    }

    protected function getButtons()
    {
        $buttons = $this->getButtonFieldset('Save');

        // Add the delete button
        $preview = new Element\Submit('preview');
        $preview->setValue('Preview');
        $preview->setAttribute('class', 'btn');
        $preview->setAttribute('id', 'previewButton');
        $buttons->add($preview);

        return $buttons;
    }

    public function getInputFilter()
    {
        $filter = parent::getInputFilter();
        $filter->get('peerGroup')->setRequired(false);
        $filter->get('hideMine')->setRequired(false);

        //pr($filter);
        return $filter;
    }
}
