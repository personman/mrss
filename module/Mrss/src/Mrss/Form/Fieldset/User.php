<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator;

class User extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->add(
            array(
                'name' => 'prefix',
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
                'name' => 'firstName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'First Name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'lastName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Last Name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'title',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Title'
                )
            )
        );

        $this->add(
            array(
                'name' => 'phone',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Phone'
                )
            )
        );

        $this->add(
            array(
                'name' => 'extension',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Extension'
                )
            )
        );

        $this->add(
            array(
                'name' => 'email',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'E-Mail Address'
                ),
                'validators' => array(
                    new Validator\EmailAddress()
                )
            )
        );

        $this->add(
            array(
                'name' => 'emailConfirm',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Confirm E-Mail Address'
                ),
                'validators' => array(
                    new Validator\EmailAddress()
                )
            )
        );
        
    }

    /**
     * Form validation
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'prefix' => array(
                'required' => true
            ),
            'firstName' => array(
                'required' => true
            ),
            'lastName' => array(
                'required' => true
            ),
            'title' => array(
                'required' => true
            ),
            'phone' => array(
                'required' => true
            ),
            'email' => array(
                'required' => true
            ),
            'emailConfirm' => array(
                'required' => true
            )
        );
    }
}
