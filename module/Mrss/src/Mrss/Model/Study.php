<?php

namespace Mrss\Model;

use \Mrss\Entity\Study as StudyEntity;

/**
 * Class Study
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Study extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Study';

    /**
     * @param $studyId
     * @return StudyEntity
     */
    public function find($studyId)
    {
        return $this->getRepository()->find($studyId);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     */
    public function findAll()
    {
        return $this->getRepository()->findBy(array(), array('name' => 'ASC'));
    }

    /**
     * Save it with Doctrine
     *
     * @param StudyEntity $study
     */
    public function save(StudyEntity $study)
    {
        $this->getEntityManager()->persist($study);

        $this->getEntityManager()->flush();
    }
}
