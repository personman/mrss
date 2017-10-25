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
        
        if ($this->studyConfig->allow_public_custom_report) {
            $this->addPublic();
        } else {
            $this->addPublicHidden();
        }
        
        $this->addDescription();
    }

    public function addPublic()
    {
        $this->add(
            array(
                'type' => 'Radio',
                'name' => 'permission',
                'options' => array(
                    'label' => 'Report Permissions',
                    'value_options' => array(
                        'private' => 'Private',
                        'public' => 'Public'
                    )
                )
            )
        );
    }


    public function addPublicHidden()
    {
        $this->add(
            array(
                'type' => 'hidden',
                'name' => 'permission',
            )
        );
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
        // Only ask for a system/network here if we're using the Network feature (Envisio)
        if ($this->systems && $this->studyConfig->use_structures) {
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
