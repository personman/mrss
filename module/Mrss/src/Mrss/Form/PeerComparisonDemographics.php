<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Regex;

class PeerComparisonDemographics extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('peerComparison');

        $this->add(
            array(
                'name' => 'state',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'State'
                ),
                'attributes' => array(
                    'id' => 'state',
                    'options' => $this->getStates(false),
                    'multiple' => 'multiple'
                )
            )
        );

        $this->add(
            array(
                'name' => 'institutional_demographics_campus_environment',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'Campus Environment'
                ),
                'attributes' => array(
                    'id' => 'institutional_demographics_campus_environment',
                    'options' => array(
                        'Urban' => 'Urban',
                        'Suburban' => 'Suburban',
                        'Rural' => 'Rural'
                    ),
                    'multiple' => 'multiple'
                )
            )
        );

        // @todo: % of Workforce Training Enrollment of Total

        $this->add(
            array(
                'name' => 'enrollment_information_unduplicated_enrollment',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Unduplicated Workforce Enrollment',
                    'help-block' => 'Specify a range (e.g., "2000 - 4000", without
                        quotes).'
                ),
                'attributes' => array(
                    'id' => 'enrollment_information_unduplicated_enrollment',
                )
            )
        );

        $this->add(
            array(
                'name' => 'revenue_total',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Total Workforce Gross Revenue',
                    'help-block' => 'Specify a range (e.g., "19000000 - 30000000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'revenue_total',
                )
            )
        );

        $this->add(
            array(
                'name' => 'institutional_demographics_total_population',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Population',
                    'help-block' => 'Specify a range (e.g., "200000 - 800000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'institutional_demographics_total_population',
                )
            )
        );

        $this->add(
            array(
                'name' => 'institutional_demographics_unemployment_rate',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Unemployment Rate',
                    'help-block' => 'Specify a range (e.g., "3 - 6", without quotes).'
                ),
                'attributes' => array(
                    'id' => 'institutional_demographics_unemployment_rate',
                )
            )
        );

        $this->add(
            array(
                'name' => 'institutional_demographics_median_household_income',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Median Household Income',
                    'help-block' => 'Specify a range (e.g., "30000 - 50000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'institutional_demographics_median_household_income',
                )
            )
        );

        $this->add($this->getButtonFieldset('Continue'));

        $this->setInputFilter($this->getInputFilterSetup());
    }

    public function getInputFilterSetup()
    {
        $filter = new InputFilter();

        // State is not required
        $state = new Input('state');
        $state->setRequired(false);
        $filter->add($state);

        $environment = new Input('institutional_demographics_campus_environment');
        $environment->setRequired(false);
        $filter->add($environment);

        $enrollment = new Input('enrollment_information_unduplicated_enrollment');
        $enrollment->setRequired(false);
        $enrollment->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($enrollment);

        $revenue = new Input('revenue_total');
        $revenue->setRequired(false);
        $revenue->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($revenue);

        $pop = new Input('institutional_demographics_total_population');
        $pop->setRequired(false);
        $pop->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($pop);

        $unemployment = new Input('institutional_demographics_unemployment_rate');
        $unemployment->setRequired(false);
        $unemployment->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($unemployment);


        $income = new Input('institutional_demographics_median_household_income');
        $income->setRequired(false);
        $income->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($income);

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
