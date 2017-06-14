<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class Report extends AbstractForm
{
    protected $systems = array();
    protected $studyConfig;
    protected $entityManager;

    public function __construct($systems = array(), $studyConfig = null, $entityManager = null)
    {
        $this->systems = $systems;
        $this->studyConfig = $studyConfig;
        $this->entityManager = $entityManager;

        // Call the parent constructor
        parent::__construct('report');
        $this->addBasicFields();
        $this->add($this->getButtonFieldset());

        $this->setInputFilter($this->getInputFilterSetup());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName('Name', null, true);
        $this->addSystems();
        $this->addDescription();
    }

    public function getInputFilterSetup()
    {
        $inputFilter = new InputFilter();

        $input = new Input('name');
        $input->setRequired(true);
        $inputFilter->add($input);

        $input = new Input('description');
        $input->setRequired(false);
        $inputFilter->add($input);

        return $inputFilter;
    }

    protected function addSystems()
    {
        if ($this->systems) {
            $field = array(
                'name' => 'system',
                //'type' => 'Select',
                'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                'options' => array(
                    'label' => ucwords($this->studyConfig->system_label),
                    'object_manager' => $this->entityManager,
                    'target_class' => 'Mrss\Entity\System'
                ),
                'attributes' => array(
                    'options' => $this->getSystemOptions()
                )
            );

            $this->add($field);

        }
    }

    protected function getSystemOptions()
    {
        $options = array();
        foreach ($this->systems as $system) {
            $options[$system->getId()] = $system->getName();
        }

        return $options;
    }
}
