<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class System extends AbstractForm
{
    public function __construct($label)
    {
        // Call the parent constructor
        parent::__construct('system');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name of ' . ucwords($label)
                ),
                'attributes' => array(
                    'id' => 'name'
                )
            )
        );

        /*$this->add(
            array(
                'name' => 'ipeds',
                'type' => 'Text',
                'options' => array(
                    'label' => 'IPEDS Unit ID'
                ),
                'attributes' => array(
                    'id' => 'ipdeds'
                )
            )
        );*/

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
