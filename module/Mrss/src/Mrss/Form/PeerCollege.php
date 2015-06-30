<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class PeerCollege extends AbstractForm
{
    /**
     * @param \Mrss\Entity\College[] $colleges
     */
    public function __construct($colleges)
    {
        // Call the parent constructor
        parent::__construct('peerCollege');

        $this->addId();

        $this->add(
            array(
                'name' => 'college',
                'type' => 'select',
                'attributes' => array(
                    'options' => $this->getCollegeOptions($colleges),
                    'id' => 'college'
                ),
                'options' => array(
                    'label' => 'Peer Institution',
                    'empty_option' => '-- Select an institution --'
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
            $options[$college->getId()] = $college->getName() . '(' . $college->getState() . ')';
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
