<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Benchmark extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmark');
        $this->addBasicFields();
        $this->addExtraFields();
        $this->add($this->getButtonFieldset());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName('Data Entry Label', 'For dynamic years, use [year], [year_minus_2], etc.');

        $this->add(
            array(
                'name' => 'reportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Report Label',
                    'help-block' => 'For dynamic years, use [year], [year_minus_2], etc.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'peerReportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Peer Report Label'
                )
            )
        );


        $this->add(
            array(
                'name' => 'descriptiveReportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Descriptive Label',
                    'help-block' => 'Used for Executive Report'
                )
            )
        );


        $this->addDescription('Data Entry Description', 'For dynamic years, use [year], [year_minus_2], etc.');

        $this->add(
            array(
                'name' => 'reportDescription',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Report Description',
                    'help-block' => 'If this field is left blank, the data entry
                    version will be used in reports. For dynamic years, use [year], [year_minus_2], etc.'
                ),
                'attributes' => array(
                    'rows' => 8
                )
            )
        );

        $this->add(
            array(
                'name' => 'timeframe',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Timeframe',
                    'help-block' => 'Shown on reports. For dynamic years, use [year], [year_minus_2], etc.'
                )
            )
        );

        // @todo: Only show this to sr admins.
        $this->add(
            array(
                'name' => 'dbColumn',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Database Column'
                )
            )
        );

        $this->add(
            array(
                'name' => 'inputType',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Input Type'
                ),
                'attributes' => array(
                    'id' => 'inputType',
                    'options' => array(
                        'number' => 'Whole Number',
                        'percent' => 'Percent',
                        'wholepercent' => 'Whole Percent',
                        'dollars' => 'Dollars',
                        'wholedollars' => 'Whole Dollars',
                        'float' => 'Float',
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'computed' => 'Computed',
                        'radio' => 'Radio',
                        'checkboxes' => 'Checkboxes'
                    )
                )
            )
        );

    }

    protected function addExtraFields()
    {
        $this->add(
            array(
                'name' => 'yearPrefix',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Year Prefix',
                    'help-block' => 'E.g., Fall, Spring, FY, academic year'
                )
            )
        );

        $this->add(
            array(
                'name' => 'yearOffset',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Year offset',
                    'help-block' => 'Collection year - x = data year.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'options',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Options',
                    'help-block' => 'One option per line.'
                ),
                'attributes' => array(
                    'rows' => 8,
                    'cols' => 80,
                )
            )
        );

        $this->add(
            array(
                'name' => 'required',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Required',
                    'help-block' => 'Required data elements can still be submitted
                    empty, but will show up on the outlier report'
                )
            )
        );

        $this->add(
            array(
                'name' => 'computed',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Computed'
                )
            )
        );

        $this->add(
            array(
                'name' => 'equation',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Equation',
                    'help-block' => $this->getEquationHelp()
                ),
                'attributes' => array(
                    'id' => 'equation',
                    'rows' => 8,
                    'cols' => 80,
                )
            )
        );

        $options = array(
            '' => 'Always compute'
        );
        $years = range(2007, date('Y') + 3);
        $years = array_combine($years, $years);
        $options = $options + $years;
        $this->add(
            array(
                'name' => 'computeAfter',
                'type' => 'Text',
                'options' => array(
                    'empty_option' => 'This is the empty option',
                    'label' => 'Compute After',
                    'help-block' => 'For some computed benchmarks, older versions
                     of the app only include the computed value, not the reported
                     values required to generate it. Use this setting to select the
                     last year where the value should not be computed. For NCCBP,
                     the value should be 2010 on many benchmarks.'
                ),
                'attributes' => array(
                    'id' => 'computeAfter',
                    'options' => $options
                )
            )
        );

        //$this->getInputFilter();


        $this->add(
            array(
                'name' => 'yearsAvailable',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'options' => array(
                    'label' => 'Years Available',
                    'value_options' => $this->getYearsAvailable()
                )
            )
        );

        $this->add(
            array(
                'name' => 'excludeFromCompletion',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Exclude From Completion Calculations'
                )
            )
        );

        $this->add(
            array(
                'name' => 'includeInNationalReport',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Include in National Report'
                )
            )
        );

        $this->add(
            array(
                'name' => 'includeInBestPerformer',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Include in Best Performers Report',
                    'help-block' => 'Also used to determine which benchmarks are ' .
                        'included in the executive report\'s strengths and weaknesses.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'highIsBetter',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'High Values Are Better'
                )
            )
        );
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }

    protected function getEquationHelp()
    {
        return '<div id="equationValidationMessage"></div><a href="/benchmark/equation"
                    data-toggle="modal" data-target="#myModal">
                        Add a benchmark to the equation
                    </a>.
                    <div id="myModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

            </div>
        </div>
    </div>';
    }
}
