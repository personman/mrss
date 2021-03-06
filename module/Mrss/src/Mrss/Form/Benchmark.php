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
        $this->add($this->getButtonFieldset('Save', false, true, $this->getConfirmText()));
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName('Data Entry Label', 'For dynamic years, use [year], [year_minus_2], etc.');

        $this->addLabels();

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
                    'rows' => 8,
                    'id' => 'reportDescription'
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
                    'label' => 'Database Column',
                    'help-block' => 'This field is required and should be unique.'
                )
            )
        );

        $this->addInputType();

    }

    protected function addLabels()
    {
        $this->add(
            array(
                'name' => 'reportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Report Label',
                    'help-block' => 'For dynamic years, use [year], [year_minus_2], etc. ' .
                        'If left blank, this defaults to the Data Entry Label.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'peerReportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Peer Report Label',
                    'help-block' => 'If left blank, this defaults to the Data Entry Label.'
                )
            )
        );


        $this->add(
            array(
                'name' => 'descriptiveReportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Descriptive Label',
                    'help-block' => 'Used for Executive Report, custom reports, ' .
                        'and other places where a stand-alone label is needed.'
                )
            )
        );
    }

    protected function addInputType()
    {
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
                        'float' => 'Number',
                        'number' => 'Whole Number',
                        'percent' => 'Percent',
                        'wholepercent' => 'Whole Percent',
                        'dollars' => 'Dollars',
                        'wholedollars' => 'Whole Dollars',
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'radio' => 'Radio',
                        'checkboxes' => 'Checkboxes',
                        'minutesseconds' => 'Minutes and Seconds'
                    )
                )
            )
        );

        $this->add(
            array(
                'name' => 'options',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Options',
                    'help-block' => 'For input types of Radio or Checkboxes. One option per line. To use a ' .
                        'numerical key, use this format on each line: "1: Value".'
                ),
                'attributes' => array(
                    'id' => 'options',
                    'rows' => 8,
                    'cols' => 80,
                )
            )
        );
    }

    protected function addExtraFields()
    {
        $this->addYearFields();
        $this->addRequired();
        $this->addComputedFields();
        $this->addYearsAvailable();
        $this->addReportCheckboxes();
    }

    protected function addComputedFields()
    {
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

        $this->add(
            array(
                'name' => 'computeIfValuesMissing',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Compute If Values Missing',
                    'help-block' => "When this box is checked, the computed benchmark's equation will be run, even " .
                        "if some source values are missing. The app assumes zeroes for any missing values. Use this " .
                        "with caution as it may result in incorrect data for certain equations."
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
    }

    protected function addYearFields()
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
    }

    protected function addRequired()
    {
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
    }

    protected function addReportCheckboxes()
    {
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
                'name' => 'includeInOtherReports',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Include in Other Reports',
                    'help-block' => 'Custom report, peer comparison, etc.'

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

    protected function addYearsAvailable()
    {
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

    protected function getConfirmText()
    {
        return "Are you sure you want to delete this benchmark? All data for it from all years will be deleted as well.";
    }
}
