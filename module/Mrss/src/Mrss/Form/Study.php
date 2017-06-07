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

        $this->addCurrentYear();

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

        $this->addOpenClosedElements();

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
}
