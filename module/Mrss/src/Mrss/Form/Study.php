<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Study extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('study');
        $this->addBasicFields();
        $this->addExtraFields();
        $this->add($this->getButtonFieldset());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName();
        $this->addDescription();

        $this->add(
            array(
                'name' => 'currentYear',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Current Year'
                ),
                'attributes' => array(
                    'options' => $this->getYearsAvailable()
                )
            )
        );

        $this->add(
            array(
                'name' => 'price',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Price'
                )
            )
        );

        $this->add(
            array(
                'name' => 'renewalPrice',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Renewal Price',
                    'help-block' => 'Renewal price trumps early-bird price.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'earlyPrice',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Early-Bird Price'
                )
            )
        );

        $this->add(
            array(
                'name' => 'earlyPriceDate',
                'type' => 'Date',
                'options' => array(
                    'label' => 'Early-Bird Price Deadline'
                )
            )
        );
    }

    protected function addExtraFields()
    {
        $this->add(
            array(
                'name' => 'pilotOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Pilot Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'enrollmentOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Enrollment Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'dataEntryOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Data Entry Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'outlierReportsOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Outlier Reports Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'reportsOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Reports Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'uPayUrl',
                'type' => 'Url',
                'options' => array(
                    'label' => 'uPay Url'
                )
            )
        );

        $this->add(
            array(
                'name' => 'uPaySiteId',
                'type' => 'Text',
                'options' => array(
                    'label' => 'uPay Site ID'
                )
            )
        );

        $this->add(
            array(
                'name' => 'logo',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Logo'
                )
            )
        );

        $this->add(
            array(
                'name' => 'googleAnalyticsKey',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Google Analytics Key'
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
}
