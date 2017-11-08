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

    /**
     * @param $email
     * @return null|UserEntity
     */
    public function findOneByEmail($email)
    {
        return $this->getRepository()->findOneBy(array('email' => $email));
    }

    /**
     * @param $id
     * @return null|UserEntity
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findByState($state)
    {
        return $this->getRepository()->findBy(
            array(
                'state' => $state
            ),
            array(
                'lastName' => 'ASC'
            )
        );
    }

    public function findByLastAccess($limit = 20)
    {
        return $this->getRepository()->findBy(
            array(),
            array(
                'lastAccess' => 'DESC'
            ),
            $limit
        );
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

    public function delete(UserEntity $user)
    {
        $this->getEntityManager()->remove($user);
    }
}
