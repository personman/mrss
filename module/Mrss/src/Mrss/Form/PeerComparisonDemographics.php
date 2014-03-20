<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

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
                    'label' => 'Total Workforce Gross Revenue'
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
                    'label' => 'Service Area Population'
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
                    'label' => 'Service Area Unemployment Rate'
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
                    'label' => 'Service Area Median Household Income'
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
        $filter->add($enrollment);

        // @todo: validate ranges

        return $filter;
    }
}
