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
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName('Name', null, true);
        $this->addDescription();
    }

    public function getInputFilter()
    {
        $inputFilter = new InputFilter();

        $input = new Input('name');
        $input->setRequired(true);
        $inputFilter->add($input);

        return $inputFilter;
    }
}
