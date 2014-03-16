<?php

namespace Mrss\Model;

use \Mrss\Entity\Percentile as PercentileEntity;

/**
 * Class Percentile
 *
 * @package Mrss\Model
 */
class Percentile extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Percentile';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function save(PercentileEntity $percentile)
    {
        $this->getEntityManager()->persist($percentile);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year)
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE Mrss\Entity\Percentile p WHERE p.year = ?1 AND p.study = ?2'
        );
        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);

        $query->execute();
    }
}
