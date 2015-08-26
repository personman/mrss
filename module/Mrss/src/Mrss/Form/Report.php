<?php

namespace Mrss\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

class Report extends AbstractForm
{
    public function __construct()
    {
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
}