<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class BenchmarkGroup extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('benchmarkGroup');
        $this->addBasicFields();
        $this->addExtraFields();
    }

    protected function addBasicFields()
    {

        $this->addId();
        $this->addName();

        $this->add(
            array(
                'name' => 'shortName',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Short Name'
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

        $this->add(
            array(
                'name' => 'url',
                'type' => 'Text',
                'options' => array(
                    'label' => 'URL'
                )
            )
        );
    }

    protected function addExtraFields()
    {

        $this->add(
            array(
                'name' => 'format',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Format'
                ),
                'attributes' => array(
                    'options' => array(
                        'one-col' => 'One column',
                        'two-col' => 'Two columns'
                    )
                )
            )
        );

        $this->addDescription();

        $this->add(
            array(
                'name' => 'useSubObservation',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Use SubObservation',
                    'help-block' => 'Replaces institution-wide data entry form with
                    a division/unit level form.'
                ),
            )
        );

        $this->add($this->getButtonFieldset());
    }
}
