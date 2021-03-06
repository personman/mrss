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
    protected $name;

    public function __construct(
        $name,
        $includeEmailConfirm = true,
        $adminControls = false,
        $entityManager = null,
        $roleSubset = false,
        $userRoleChoices = array('viewer', 'contact', 'data'),
        $editingSelf = false
    ) {
        $this->includeEmailConfirm = $includeEmailConfirm;
        $this->name = $name;

        parent::__construct($name);

        $this->addContactFields();

        if ($adminControls) {
            $this->addAdminControls($entityManager);

        } elseif ($roleSubset && !$editingSelf) {
            $this->addNonAdminControls($userRoleChoices);

        }
    }

    protected function addContactFields()
    {
        $this->addNameFields();
        $this->addPhoneFields();
        $this->addEmailFields();
    }

    protected function getUserRoles()
    {
        $userRoles = array(
            'data' => 'Data Manager',
            'contact' => 'Membership Coordinator',
            'viewer' => 'View Reports Only',
            'system_admin' => 'System Administrator',
            'system_viewer' => 'System Viewer',
            'admin' => 'Super-Admin'
        );

        return $userRoles;
    }

    protected function addNameFields()
    {
        $name = $this->name;

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
    }

    protected function addPhoneFields()
    {
        $this->add(
            array(
                'name' => 'phone',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Phone'
                ),
                'attributes' => array(
                    'id' => $this->name . '-phone'
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
                    'id' => $this->name . '-extension'
                )
            )
        );
    }

    protected function addEmailFields()
    {
        $this->add(
            array(
                'name' => 'email',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'E-Mail Address'
                ),
                'attributes' => array(
                    'id' => $this->name . '-email'
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
                        'id' => $this->name . '-emailConfirm'
                    )
                )
            );
        }
    }

    protected function addAdminControls($entityManager)
    {


        $this->add(
            array(
                'name' => 'role',
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'Role',
                    'help-block' => $this->getRoleHelp($this->getUserRoles())
                ),
                'attributes' => array(
                    'options' => $this->getUserRoles()
                )
            )
        );

        if ($entityManager) {
            $this->add(
                array(
                    'type' => 'DoctrineORMModule\Form\Element\EntityMultiCheckbox',
                    'name' => 'studies',
                    'options' => array(
                        'label' => 'Studies',
                        'object_manager' => $entityManager,
                        'target_class'   => 'Mrss\Entity\Study',
                        'property'       => 'name',
                    ),
                )
            );
        }

    }

    protected function addNonAdminControls($userRoleChoices)
    {
        $userRoles = array(
            'data' => 'Data Manager',
            'contact' => 'Membership Coordinator',
            'viewer' => 'View Reports Only'
        );
        foreach ($userRoles as $key => $label) {
            if (!in_array($key, $userRoleChoices)) {
                unset($userRoles[$key]);
                unset($label);
            }
        }

        $this->add(
            array(
                'name' => 'role',
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'Role',
                    'help-block' => $this->getRoleHelp($userRoles)
                ),
                'attributes' => array(
                    'options' => $userRoles
                )
            )
        );
    }

    protected function getRoleHelp($roles)
    {
        $descriptions = array();

        if (!empty($roles['viewer'])) {
            $descriptions[] = '<em>View Reports Only</em> users can only view reports and create custom reports.';
        }

        if (!empty($roles['contact'])) {
            $descriptions[] = '<em>Membership Coordinators</em> can view reports, renew membership, and
            manage users.';
        }

        if (!empty($roles['data'])) {
            $descriptions[] = '<em>Data Managers</em> can view reports, renew memberships,
            manage users, and enter data.';
        }


        return implode(' ', $descriptions);
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
                'required' => false
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
            'studies' => array(
                'required' => false,
            )
        );

        if ($this->includeEmailConfirm) {
            $emailValidator = new Identical('email');
            $emailValidator->setMessage(
                'E-Mail addresses do not match',
                Identical::NOT_SAME
            );

            $spec['emailConfirm'] = array(
                'required' => true,
                'validators' => array(
                    new EmailValidator(),
                    $emailValidator
                )
            );
        }

        return $spec;
    }
}
