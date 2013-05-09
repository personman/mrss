<?php

namespace Mrss\Model;

use \Mrss\Entity\User as UserEntity;

/**
 * Class User model
 *
 * @package Mrss\Model
 */
class User extends AbstractModel
{
    protected $entity = 'Mrss\Entity\User';

    public function findOneByEmail($email)
    {
        return $this->getRepository()->findOneBy(array('email' => $email));
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all colleges, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('lastName' => 'ASC'));
        return $c;
    }

    public function save(UserEntity $user)
    {
        $this->getEntityManager()->persist($user);

        // Flush here or leave it to some other code?
    }
}
