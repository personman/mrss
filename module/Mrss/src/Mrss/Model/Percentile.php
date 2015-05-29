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

    /**
     * @param $benchmark
     * @param $year
     * @param null $system
     * @return PercentileEntity[]
     */
    public function findByBenchmarkAndYear($benchmark, $year, $system = null)
    {
        if (empty($system)) {
            $system = null;
        }

        return $this->getRepository()->findBy(
            array(
                'benchmark' => $benchmark,
                'year' => $year,
                'system' => $system
            ),
            array(
                'percentile' => 'ASC'
            )
        );
    }

    public function findByBenchmarkYearAndPercentile($benchmark, $year, $percentile)
    {
        return $this->getRepository()->findOneBy(
            array(
                'benchmark' => $benchmark,
                'year' => $year,
                'percentile' => $percentile
            )
        );
    }

    /**
     * @param $benchmark
     * @param $percentile
     * @return PercentileEntity[]
     */
    public function findByBenchmarkAndPercentile($benchmark, $percentile)
    {
        return $this->getRepository()->findBy(
            array(
                'benchmark' => $benchmark,
                'percentile' => $percentile,
                'system' => null
            ),
            array(
                'year' => 'ASC'
            )
        );
    }

    public function save(PercentileEntity $percentile)
    {
        $this->getEntityManager()->persist($percentile);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year, $system = null)
    {
        $dql = 'DELETE Mrss\Entity\Percentile p
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
