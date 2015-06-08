<?php

namespace Mrss\Form;

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
        $this->addName();
        $this->addDescription();
    }
}
