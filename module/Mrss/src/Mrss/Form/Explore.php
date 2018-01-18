<?php

namespace Mrss\Form;

use Mrss\Entity\College;
use Zend\Form\Element;

class Explore extends AbstractForm
{
    protected $studyConfig;

    public function __construct(
        $benchmarks,
        $colleges,
        $years,
        $peerGroups,
        $includeTrends,
        $allBreakpoints,
        $studyConfig
    ) {
        // Call the parent constructor
        parent::__construct('explore');

        rsort($years);
        $this->studyConfig = $studyConfig;

        $this->addBasicFields($includeTrends);
        $this->addMoreBasicFields($years);
        $this->addBenchmarkSelects($benchmarks);
        $this->addHiddenBenchmarkSelects($benchmarks);
        $this->addPeerGroupDropdown($peerGroups, $colleges);
        $this->addAdvancedFields($benchmarks, $years);
        $this->addMoreAdvancedFields($allBreakpoints);

        $this->add($this->getButtons());
    }

    protected function addBasicFields($includeTrends)
    {
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
                'name' => 'multiTrend',
                'type' => 'Hidden',
                'attributes' => array(
                    'id' => 'multiTrend'
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
                    //'class' => 'selectpicker',
                    'options' => self::getPresentationOptions($includeTrends)
                )
            )
        );
    }

    protected function addMoreBasicFields($years)
    {
        $years = array_combine($years, $years);
        $this->add(
            array(
                'name' => 'width',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Width'
                ),
                'attributes' => array(
                    'id' => 'width',
                    'options' => array(
                        'half' => 'Half',
                        'full' => 'Full'
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

    protected function addHiddenBenchmarkSelects($benchmarks)
    {
        foreach (range('a', 'g') as $key) {
            $this->add(
                array(
                    'name' => 'benchmark2' . $key,
                    'type' => 'Zend\Form\Element\Select',
                    'allow_empty' => true,
                    'options' => array(
                        'label' => 'Measure ' . $key,
                        'empty_option' => 'None'
                    ),
                    'attributes' => array(
                        'options' => $benchmarks,
                        'id' => 'benchmark2' . $key,
                        'class' => 'extraBenchmark'
                    )
                )
            );
        }
    }

    protected function addPeerGroupDropdown($peerGroups, $colleges)
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

        $this->addExtraPeerFields($colleges);


    }

    /**
     * @param College[] $colleges
     */
    protected function addExtraPeerFields($colleges)
    {
        if (count($colleges)) {
            $collegeOptions = array();
            foreach ($colleges as $college) {
                $collegeOptions[$college->getId()] = $college->getNameAndState();
            }

            $this->add(
                array(
                    'name' => 'colleges',
                    'type' => 'Zend\Form\Element\MultiCheckbox',
                    'options' => array(
                        'label' => 'Peers',
                        'value_options' => $collegeOptions
                    ),
                    'attributes' => array(
                        'id' => 'colleges'
                    )
                )
            );
        }

        if ($this->studyConfig->use_structures) {
            $this->add(
                array(
                    'name' => 'peerGroupAverage',
                    'type' => 'Zend\Form\Element\Checkbox',
                    'options' => array(
                        'label' => 'Peer Group Average'
                    ),
                    'attributes' => array(
                        'id' => 'peerGroupAverage'
                    )
                )
            );
        }


        $this->add(
            array(
                'name' => 'makePeerCohort',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => 'Include Only Peers with Data for All Years'
                ),
                'attributes' => array(
                    'id' => 'makePeerCohort'
                )
            )
        );
    }

    protected function addAdvancedFields($benchmarks, $years)
    {
        $years = array_combine($years, $years);

        $this->add(
            array(
                'name' => 'startYear',
                'allow_empty' => true,
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Start Year',
                    'empty_option' => '',
                ),
                'attributes' => array(
                    'options' => $years,
                    'id' => 'startYear'
                )
            )
        );

        $this->add(
            array(
                'name' => 'endYear',
                'type' => 'Zend\Form\Element\Select',
                'allow_empty' => true,
                'options' => array(
                    'label' => 'End Year',
                    'empty_option' => '',
                ),
                'attributes' => array(
                    'options' => $years,
                    'id' => 'endYear'
                )
            )
        );

        if (true) {
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
        }

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

        $label = 'Hide National Data';
        if ($this->studyConfig->use_structures) {
            $label = 'Hide Network Data';
        }

        $this->add(
            array(
                'name' => 'hideNational',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => $label
                ),
                'attributes' => array(
                    'id' => 'hideNational'
                )
            )
        );
    }

    protected function addMoreAdvancedFields($allBreakpoints)
    {
        if (!$this->studyConfig->enable_min_max_breakpoints) {
            unset($allBreakpoints[0]);
            unset($allBreakpoints[100]);
        }

        $allBreakpoints = array_combine($allBreakpoints, $allBreakpoints);

        if (isset($allBreakpoints[0])) {
            $allBreakpoints[0] = 'Minimum';
            $allBreakpoints[100] = 'Maximum';
        }

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


        $this->add(
            array(
                'name' => 'percentScaleZoom',
                'type' => 'Zend\Form\Element\Checkbox',
                'options' => array(
                    'label' => 'Percent Scale Other than 0-100',
                    'help-block' => 'By default the scale for percentages is 0-100%. ' .
                        'Check this box to allow the chart to select a scale that shows more detail.'
                ),
                'attributes' => array(
                    'id' => 'percentScaleZoom'
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
        $filter->get('startYear')->setRequired(false);
        $filter->get('endYear')->setRequired(false);
        $filter->get('hideMine')->setRequired(false);
        $filter->get('percentiles')->setRequired(false);

        if ($filter->has('peerGroupAverage')) {
            $filter->get('peerGroupAverage')->setRequired(false);
        }


        if ($filter->has('colleges')) {
            $filter->get('colleges')->setRequired(false);
        }

        foreach (range('a', 'g') as $key) {
            $filter->get('benchmark2' . $key)->setRequired(false);
        }
        return $filter;
    }

    public static function getPresentationOptions($includeTrends)
    {
        $options = array(
            'line' => 'Trend Line',
            'bar' => 'Percentile Bar Chart',
            'scatter' => 'Scatter Plot',
            'bubble' => 'Bubble Plot',
            'peer' =>'Peer Comparison',
            'text' => 'Text'
        );

        // Remove trend option if there's not enough data
        if (empty($includeTrends)) {
            unset($options['line']);
        }

        return $options;
    }
}
