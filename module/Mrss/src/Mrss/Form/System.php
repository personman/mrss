<?php

namespace Mrss\Form;

class System extends AbstractForm
{
    public function __construct($label)
    {
        // Call the parent constructor
        parent::__construct('system');

        $this->addId();
        $this->addName('Name of ' . ucwords($label));
        $this->addAddressFields();


        $this->add(
            array(
                'name' => 'joinSetting',
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'Join Setting',
                ),
                'attributes' => array(
                    'options' => array(
                        'open' => 'Open - Anyone can join',
                        'private' => 'Must request to join'
                    )
                )
            )
        );

        $this->addCurrentYear();
        $this->addOpenClosedElements();

        $this->add($this->getButtonFieldset());
    }
}
