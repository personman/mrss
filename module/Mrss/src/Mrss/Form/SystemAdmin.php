<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Mrss\Entity\System;
use Mrss\Entity\User;

/**
 * Class SystemAdmin
 *
 * This form is for making promoting users to system admin
 *
 * @package Mrss\Form
 */
class SystemAdmin extends AbstractForm
{
    protected $system;

    public function __construct(System $system)
    {
        $this->system = $system;

        // Call the parent constructor
        parent::__construct('system_admin');

        $this->add(
            array(
                'name' => 'system_id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'user_id',
                'type' => 'Select',
                'options' => array(
                    'label' => 'User to promote'
                ),
                'attributes' => array(
                    'options' => $this->getUserOptions()
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }

    public function getUserOptions()
    {
        $options = array();
        foreach ($this->system->getColleges() as $college) {
            $users = array();

            foreach ($college->getUsers() as $user) {
                $users[$user->getId()] = $user->getFullName();
            }

            $options[$college->getId()] = array(
                'label' => $college->getName(),
                'options' => $users
            );
        }

        return $options;
    }
}
