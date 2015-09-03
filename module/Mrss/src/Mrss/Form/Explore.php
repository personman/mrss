<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class Explore extends AbstractForm
{

    public function __construct($benchmarks, $colleges, $years, $peerGroups, $includeTrends, $allBreakpoints)
    {
        // Call the parent constructor
        parent::__construct('explore');

        rsort($years);

        $years = array_combine($years, $years);

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'isCancel',
                'type' => 'Hidden',
                'attributes' => array(
                    'id' => 'isCancel'
                )
            )
        );

        $this->add(
            array(
                'name' => 'isPreview',
                'type' => 'Hidden',
                'attributes' => array(
                    'id' => 'isPreview'
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
                    'options' => $this->getPresentationOptions($includeTrends)
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
                'name' => 'subtitle',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Subtitle'
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

        $allBreakpoints = array_combine($allBreakpoints, $allBreakpoints);
        $this->add(
            array(
                'name' => 'percentiles',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'options' => array(
                    'label' => 'Percentiles',
                    'value_options' => $allBreakpoints
                ),
                'attributes' => array(
                    'id' => 'percentiles'
                )
            )
        );

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
                    'empty_option' => 'None',
                    'help-block' => '<a href="/peer-groups" target="_blank">Create and manage peer groups</a>'
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

        // Add the preview button
        $preview = new Element\Submit('preview');
        $preview->setValue('Preview');
        $preview->setAttribute('class', 'btn');
        $preview->setAttribute('id', 'previewButton');
        $buttons->add($preview);

        // Add the cancel button
        $cancel = new Element\Submit('cancel');
        $cancel->setValue('Cancel');
        $cancel->setAttribute('class', 'btn btn-danger');
        $cancel->setAttribute('id', 'cancelButton');
        $buttons->add($cancel);

        return $buttons;
    }

    public function getInputFilter()
    {
        $filter = parent::getInputFilter();
        $filter->get('peerGroup')->setRequired(false);
        $filter->get('hideMine')->setRequired(false);
        $filter->get('percentiles')->setRequired(false);

        //pr($filter);
        return $filter;
    }

    protected function getPresentationOptions($includeTrends)
    {
        $minYears = 3;

        $options = array(
            'line' => 'Trend Line',
            'bar' => 'Percentile Bar Chart',
            'scatter' => 'Scatter Plot',
            'bubble' => 'Bubble Plot',
            'text' => 'Text'
        );

        // Remove trend option if there's not enough data
        //if (count($years) < $minYears) {
            //unset($options['line']);
        //}
        if (empty($includeTrends)) {
            unset($options['line']);
        }

        return $options;
    }
}
