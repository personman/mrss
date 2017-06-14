<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class PeerCollege extends AbstractForm
{
    /**
     * @param \Mrss\Entity\College[] $colleges
     */
    public function __construct($colleges, $studyConfig)
    {
        // Call the parent constructor
        parent::__construct('peerCollege');

        $this->addId();

        $institution_label = $studyConfig->institution_label;
        $aOrAn = 'a';
        $firstLetter = strtolower(substr($institution_label, 0, 1));
        if (in_array($firstLetter, array('a', 'e', 'i', 'o', 'u'))) {
            $aOrAn = 'an';
        }
        $this->add(
            array(
                'name' => 'college',
                'type' => 'select',
                'attributes' => array(
                    'options' => $this->getCollegeOptions($colleges),
                    'id' => 'college'
                ),
                'options' => array(
                    'label' => 'Peer ' . ucwords($institution_label),
                    'empty_option' => "-- Select $aOrAn " . strtolower($studyConfig->institution_label) . ' --'
                ),

            )
        );

        $this->add($this->getButtonFieldset());

        $this->setInputFilter($this->getInputFilterSetup());
    }

    public function getCollegeOptions($colleges)
    {
        $options = array();
        foreach ($colleges as $college) {
            $options[$college->getId()] = $college->getName() . ' (' . $college->getState() . ')';
        }

        return $options;
    }

    public function getInputFilterSetup()
    {
        $inputFilter = new InputFilter();

        /*$input = new Input('description');
        $input->setRequired(false);
        $inputFilter->add($input);
        */
        return $inputFilter;
    }
}
