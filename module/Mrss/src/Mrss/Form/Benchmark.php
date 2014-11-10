<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Benchmark extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmark');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Data Entry Label'
                )
            )
        );

        $this->add(
            array(
                'name' => 'reportLabel',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Report Label'
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



        $this->add(
            array(
                'name' => 'description',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Data Entry Description'
                ),
                'attributes' => array(
                    'rows' => 8
                )
            )
        );

        $this->add(
            array(
                'name' => 'reportDescription',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Report Description',
                    'help-block' => 'If this field is left blank, the data entry
                    version will be used in reports.'
                ),
                'attributes' => array(
                    'rows' => 8
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
                        'computed' => 'Computed',
                        'radio' => 'Radio'
                    )
                )
            )
        );



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
                'type' => 'Text',
                'options' => array(
                    'label' => 'Options'
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
                'type' => 'Text',
                'options' => array(
                    'label' => 'Equation',
                    'help-block' => '<a href="/benchmark/equation"
                    data-toggle="modal" data-target="#myModal">
                        Add a benchmark to the equation
                    </a>.
                    <div class="modal fade" id="myModal" tabindex="-1"
                    role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Select a Benchmark</h3>
      </div>
      <div class="modal-body">
        <!-- content will be loaded here -->
      </div>
    </div>'
                ),
                'attributes' => array(
                    'id' => 'equation'
                )
            )
        );

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
                    'label' => 'Include in Best Performers Report'
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

        $this->add($this->getButtonFieldset());
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }
}
