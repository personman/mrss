<?php

namespace Mrss\Form;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Regex;
use Mrss\Entity\Study;

class PeerComparisonDemographics extends AbstractForm
{
    protected $study;

    public function __construct(Study $study, $studyConfig = null)
    {
        $this->study = $study;

        if ($studyConfig) {
            $this->setIncludeCanada($studyConfig->include_canada);
        }

        // Call the parent constructor
        parent::__construct('peerComparison');

        $this->addStates();
        $this->addCriteria();

        $this->add($this->getButtonFieldset('Continue', true));

        $this->setInputFilter($this->getInputFilterSetup());
    }

    protected function addStates()
    {
        $this->add(
            array(
                'name' => 'states',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'State'
                ),
                'attributes' => array(
                    'id' => 'states',
                    'options' => $this->getStates(false),
                    'multiple' => 'multiple'
                )
            )
        );
    }

    protected function addCriteria()
    {
        foreach ($this->study->getCriteria() as $criterion) {
            $benchmark = $criterion->getBenchmark();

            $type = 'Text';
            $options = null;
            if ($benchmark->getInputType() == 'radio') {
                $type = 'Select';
                $options = $benchmark->getOptions();
                $options = explode("\n", $options);
                $options = array_map('trim', $options);
                $options = array_combine($options, $options);
            }

            $field = array(
                'name' => $benchmark->getDbColumn(),
                'type' => $type,
                'required' => false,
                'options' => array(
                    'label' => $criterion->getName()
                ),
                'attributes' => array(
                    'id' => $benchmark->getDbColumn()
                )
            );

            if (!empty($options)) {
                $field['attributes']['options'] = $options;
                $field['attributes']['multiple'] = 'multiple';
            }

            if ($help = $criterion->getHelpText()) {
                $field['options']['help-block'] = $help;
            }

            $this->add($field);
        }
    }

    public function getInputFilterSetup()
    {
        $filter = new InputFilter();

        // State is not required
        $state = new Input('states');
        $state->setRequired(false);
        $filter->add($state);

        foreach ($this->study->getCriteria() as $criterion) {
            $benchmark = $criterion->getBenchmark();

            $field = new Input($benchmark->getDbColumn());
            $field->setRequired(false);

            if ($benchmark->getInputType() != 'radio') {
                $field->getValidatorChain()->attach($this->getRangeValidator());
            }

            $filter->add($field);
        }

        return $filter;
    }

    public function getRangeValidator()
    {
        // Should match "123 - 23434" and "123-23434
        $validator = new Regex('/^\d+\s?-\s?\d+$/');
        $validator->setMessage(
            'Use the format "100 - 200". Do not include commas.',
            Regex::NOT_MATCH
        );

        return $validator;
    }
}
