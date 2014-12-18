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
     * @param $id
     * @return \Mrss\Entity\Study
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
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
