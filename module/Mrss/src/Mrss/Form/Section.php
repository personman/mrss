<?php

namespace Mrss\Form;

class Section extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('section');
        $this->addBasicFields();
        $this->add($this->getButtonFieldset());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName();
        $this->addDescription();


        $this->add(
            array(
                'name' => 'price',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Price'
                )
            )
        );
    }
}
