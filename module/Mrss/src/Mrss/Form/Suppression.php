<?php

namespace Mrss\Form;

class Suppression extends AbstractForm
{
    protected $study;

    public function __construct($study, $subscription)
    {
        $this->study = $study;

        // Call the parent constructor
        parent::__construct('suppression');

        $options = $this->getOptions();

        $this->add(
            array(
                'name' => 'suppressions',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'options' => array(
                    'label' => 'Forms to Suppress',
                    'value_options' => $options
                )
            )
        );

        $this->add(
            array(
                'name' => 'subscription',
                'type' => 'Hidden',
            )
        );

        // Set values
        $this->get('subscription')->setValue($subscription->getId());


        $this->add($this->getButtonFieldset());
    }

    public function getOptions()
    {
        $options = array();
        foreach ($this->study->getBenchmarkGroups() as $benchmarkGroup) {
            $options[$benchmarkGroup->getId()] = $benchmarkGroup->getName();
        }

        return $options;
    }
}
