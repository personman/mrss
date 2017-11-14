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

    protected function addAddressFields()
    {
        $this->add(
            array(
                'name' => 'address',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address'
                ),
                'attributes' => array(
                    'id' => 'address'
                )
            )
        );

        $this->add(
            array(
                'name' => 'address2',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address 2'
                ),
                'attributes' => array(
                    'id' => 'address2'
                )
            )
        );

        $this->add(
            array(
                'name' => 'city',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'City'
                ),
                'attributes' => array(
                    'id' => 'city'
                )
            )
        );

        $this->add(
            array(
                'name' => 'state',
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'State'
                ),
                'attributes' => array(
                    'options' => $this->getStates(),
                    'id' => 'state'
                )
            )
        );

        $this->add(
            array(
                'name' => 'zip',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Zip Code'
                ),
                'attributes' => array(
                    'id' => 'zip'
                )
            )
        );
    }
}
