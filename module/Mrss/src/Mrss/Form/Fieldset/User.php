<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\EmailAddress as EmailValidator;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\Regex;

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
                    'id' => $name . '-prefix',
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
                ),
                'attributes' => array(
                    'id' => $name . '-firstName'
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
                ),
                'attributes' => array(
                    'id' => $name . '-lastName'
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
                ),
                'attributes' => array(
                    'id' => $name . '-title'
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
                ),
                'attributes' => array(
                    'id' => $name . '-phone'
                )
            )
        );

        $this->add(
            array(
                'name' => 'extension',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Extension'
                ),
                'attributes' => array(
                    'id' => $name . '-extension'
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
                'attributes' => array(
                    'id' => $name . '-email'
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
                'attributes' => array(
                    'id' => $name . '-emailConfirm'
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
        $emailConfirmValidator = new Identical('email');
        $emailConfirmValidator->setMessage(
            'E-Mail addresses do not match',
            Identical::NOT_SAME
        );

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
                'required' => true,
                'validators' => array(
                    new EmailValidator()
                )
            ),
            'emailConfirm' => array(
                'required' => true,
                'validators' => array(
                    new EmailValidator(),
                    $emailConfirmValidator
                )
            )
        );
    }
}
