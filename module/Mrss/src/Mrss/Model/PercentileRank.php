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

    public function findOneByCollegeBenchmarkAndYear(
        $college,
        $benchmark,
        $year,
        $system = null
    ) {
        return $this->getRepository()->findOneBy(
            array(
                'college' => $college,
                'benchmark' => $benchmark,
                'year' => $year,
                'system' => $system
            )
        );
    }

    public function save(PercentileRankEntity $PercentileRank)
    {
        $this->getEntityManager()->persist($PercentileRank);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year, $system = null)
    {
        $dql = 'DELETE Mrss\Entity\PercentileRank p
            WHERE p.year = ?1
            AND p.study = ?2';

        if ($system) {
            $dql .= ' AND p.system = ?3';
        } else {
            $dql .= ' AND p.system IS NULL';
        }


        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);

        if ($system) {
            $query->setParameter(3, $system);
        }

        $query->execute();
    }
}
