<?php

namespace Mrss\Model;

use \Mrss\Entity\PercentileRank as PercentileRankEntity;

/**
 * Class PercentileRank
 *
 * @package Mrss\Model
 */
class PercentileRank extends AbstractModel
{
    protected $entity = 'Mrss\Entity\PercentileRank';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function save(PercentileRankEntity $PercentileRank)
    {
        $this->getEntityManager()->persist($PercentileRank);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year)
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE Mrss\Entity\PercentileRank p WHERE p.year = ?1 AND p.study = ?2'
        );
        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);

        $query->execute();
    }
}
