<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Study extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('study');

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

        $this->add($this->getButtonFieldset());
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }
}
