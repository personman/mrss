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
                    'label' => 'Name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'description',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Description'
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
                'name' => 'options',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Options'
                )
            )
        );

        $this->add(
            array(
                'name' => 'equation',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Equation',
                    'help-block' => '<a href="/benchmarks/equation"
                    data-toggle="modal" data-target="#myModal">
                        Add a benchmark to the equation
                    </a>.
                    <div class="modal hide fade" id="myModal" tabindex="-1"
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

        $this->add($this->getButtonFieldset());
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }
}
