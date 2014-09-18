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
    protected $includeEmailConfirm = true;

    public function __construct(
        $name,
        $includeEmailConfirm = true,
        $adminControls = false,
        $em = null
    ) {
        $this->includeEmailConfirm = $includeEmailConfirm;

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

        if ($this->includeEmailConfirm) {
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

        if ($adminControls) {
            $this->add(
                array(
                    'name' => 'role',
                    'type' => 'Select',
                    'required' => true,
                    'options' => array(
                        'label' => 'Role'
                    ),
                    'attributes' => array(
                        'options' => array(
                            'data' => 'Data Manager',
                            'contact' => 'Membership Coordinator',
                            'viewer' => 'View Reports Only',
                            'system_admin' => 'State System Administrator',
                            'admin' => 'NHEBI Staff'
                        )
                    )
                )
            );

            if ($em) {
                $this->add(
                    array(
                        'type' => 'DoctrineORMModule\Form\Element\EntityMultiCheckbox',
                        'name' => 'studies',
                        'options' => array(
                            'label' => 'Studies',
                            'object_manager' => $em,
                            'target_class'   => 'Mrss\Entity\Study',
                            'property'       => 'name',
                        ),
                    )
                );
            }
        }
    }

    /**
     * Form validation
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $spec =  array(
            'same' => array(
                'required' => false
            ),
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
            )
        );

        if ($this->includeEmailConfirm) {
            $emailConfirmValidator = new Identical('email');
            $emailConfirmValidator->setMessage(
                'E-Mail addresses do not match',
                Identical::NOT_SAME
            );

            $spec['emailConfirm'] = array(
                'required' => true,
                'validators' => array(
                    new EmailValidator(),
                    $emailConfirmValidator
                )
            );
        }

        return $spec;
    }
}
