<?php

namespace Mrss\Model;

use Doctrine\ORM\EntityManager;

abstract class AbstractModel
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected $repository;


    /**
     * Set the entity manager
     *
     * @param EntityManager $entityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    protected function getRepository()
    {
        if (null === $this->repository) {
            $this->repository = $this->getEntityManager()->getRepository($this->entity);
        }

        return $this->repository;
    }

}