<?php

namespace Mrss\Form;

class SubscriptionModule extends AbstractForm
{
    protected $sections;

    public function __construct($sections)
    {
        $this->sections = $sections;

        // Call the parent constructor
        parent::__construct('benchmark');
        $this->addModuleCheckboxes();
        $this->add($this->getButtonFieldset('Continue'));
    }

    protected function addModuleCheckboxes()
    {
        $this->add(
            array(
                'name' => 'sections',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'options' => array(
                    'label' => 'Modules',
                    'value_options' => $this->getSections()
                )
            )
        );
    }

    protected function getSections()
    {
        return $this->sections;
    }
}
