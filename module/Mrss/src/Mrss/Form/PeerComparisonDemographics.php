<?php

namespace Mrss\Form;

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

        $this->add(
            array(
                'name' => 'environments',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'Campus Environment'
                ),
                'attributes' => array(
                    'id' => 'environments',
                    'options' => array(
                        'Urban' => 'Urban',
                        'Suburban' => 'Suburban',
                        'Rural' => 'Rural'
                    ),
                    'multiple' => 'multiple'
                )
            )
        );

        $this->add(
            array(
                'name' => 'facultyUnionized',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'Faculty Unionized'
                ),
                'attributes' => array(
                    'id' => 'facultyUnionized',
                    'options' => array(
                        'Yes' => 'Yes',
                        'No' => 'No'
                    ),
                    'multiple' => 'multiple'
                )
            )
        );

        $this->add(
            array(
                'name' => 'staffUnionized',
                'type' => 'Select',
                'required' => false,
                'options' => array(
                    'label' => 'Staff Unionized'
                ),
                'attributes' => array(
                    'id' => 'staffUnionized',
                    'options' => array(
                        'Yes' => 'Yes',
                        'No' => 'No'
                    ),
                    'multiple' => 'multiple'
                )
            )
        );

        // @todo: % of Workforce Training Enrollment of Total

        $this->add(
            array(
                'name' => 'workforceEnrollment',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Unduplicated Workforce Enrollment',
                    'help-block' => 'Specify a range (e.g., "2000 - 4000", without
                        quotes).'
                ),
                'attributes' => array(
                    'id' => 'workforceEnrollment',
                )
            )
        );

        $this->add(
            array(
                'name' => 'workforceRevenue',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Total Workforce Gross Revenue',
                    'help-block' => 'Specify a range (e.g., "19000000 - 30000000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'workforceRevenue',
                )
            )
        );

        $this->add(
            array(
                'name' => 'serviceAreaPopulation',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Population',
                    'help-block' => 'Specify a range (e.g., "200000 - 800000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'serviceAreaPopulation',
                )
            )
        );

        $this->add(
            array(
                'name' => 'serviceAreaUnemployment',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Unemployment Rate',
                    'help-block' => 'Specify a range (e.g., "3 - 6", without quotes).'
                ),
                'attributes' => array(
                    'id' => 'serviceAreaUnemployment',
                )
            )
        );

        $this->add(
            array(
                'name' => 'serviceAreaMedianIncome',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Service Area Median Household Income',
                    'help-block' => 'Specify a range (e.g., "30000 - 50000",
                        without quotes).'
                ),
                'attributes' => array(
                    'id' => 'serviceAreaMedianIncome',
                )
            )
        );

        $this->add($this->getButtonFieldset('Continue', true));

        $this->setInputFilter($this->getInputFilterSetup());
    }

    public function getInputFilterSetup()
    {
        $filter = new InputFilter();

        // State is not required
        $state = new Input('states');
        $state->setRequired(false);
        $filter->add($state);

        $environment = new Input('environments');
        $environment->setRequired(false);
        $filter->add($environment);

        $facultyUnionized = new Input('facultyUnionized');
        $facultyUnionized->setRequired(false);
        $filter->add($facultyUnionized);

        $staffUnionized = new Input('staffUnionized');
        $staffUnionized->setRequired(false);
        $filter->add($staffUnionized);

        $enrollment = new Input('workforceEnrollment');
        $enrollment->setRequired(false);
        $enrollment->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($enrollment);

        $revenue = new Input('workforceRevenue');
        $revenue->setRequired(false);
        $revenue->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($revenue);

        $pop = new Input('serviceAreaPopulation');
        $pop->setRequired(false);
        $pop->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($pop);

        $unemployment = new Input('serviceAreaUnemployment');
        $unemployment->setRequired(false);
        $unemployment->getValidatorChain()->attach($this->getRangeValidator());
        $filter->add($unemployment);


        $income = new Input('serviceAreaMedianIncome');
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
