<?php

namespace Mrss\Model;

use \Mrss\Entity\Outlier as OutlierEntity;
use Zend\Debug\Debug;

/**
 * Class Outlier
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Outlier extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Outlier';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }


    public function save(OutlierEntity $outlier)
    {
        $this->getEntityManager()->persist($outlier);
    }

    public function delete(OutlierEntity $outlier)
    {
        $this->getEntityManager()->remove($outlier);
        $this->getEntityManager()->flush();
    }

    public function deleteByStudyAndYear($studyId, $year)
    {
        die('deleteByStudyAndYear not implemented');
    }
}
