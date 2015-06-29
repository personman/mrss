<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class PeerGroup extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('peer_group');
        $this->addBasicFields();
        $this->add($this->getButtonFieldset());

        $this->setInputFilter($this->getInputFilterSetup());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName('Name', null, true);
    }

    public function getInputFilterSetup()
    {
        $inputFilter = new InputFilter();

        $input = new Input('name');
        $input->setRequired(true);
        $inputFilter->add($input);

        return $inputFilter;
    }
}
