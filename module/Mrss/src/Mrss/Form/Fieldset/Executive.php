<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Regex;

class Executive extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('executive');

        $this->setLabel('Executive Contact');

        $this->add(
            array(
                'name' => 'execTitle',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Executive Title',
                    'help-block' => 'For example: President or Chancellor'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execSalutation',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Prefix'
                ),
                'attributes' => array(
                    'options' => array(
                        '' => 'Select Prefix',
                        'Dr.' => 'Dr.',
                        'Mr.' => 'Mr.',
                        'Ms.' => 'Ms.'
                    )
                )
            )
        );


        $this->add(
            array(
                'name' => 'execFirstName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'First Name'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execLastName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Last Name'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execEmail',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'E-Mail Address'
                ),
            )
        );

    }

    public function getInputFilterSpecification()
    {
        return array(
            'execTitle' => array(
                'required' => false
            ),
            'execSalutation' => array(
                'required' => false,
            ),
            'execFirstName' => array(
                'required' => false
            ),
            'execLastName' => array(
                'required' => false
            ),
            'execEmail' => array(
                'required' => false
            ),
        );
    }
}
