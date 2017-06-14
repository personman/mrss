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
     * @param $breakpoints
     * @param null $system
     * @param $forPercentChange
     * @return PercentileEntity[]
     */
    public function findByBenchmarkAndYear($benchmark, $year, $breakpoints, $system = null, $forPercentChange = false)
    {
        if (empty($system)) {
            $system = null;
        }

        $results = $this->getRepository()->findBy(
            array(
                'benchmark' => $benchmark,
                'year' => $year,
                'system' => $system,
                'percentile' => $breakpoints,
                'forPercentChange' => $forPercentChange
            ),
            array(
                'percentile' => 'ASC'
            )
        );

        return $results;
    }

    public function findByBenchmarkYearAndPercentile($benchmark, $year, $percentile, $forPercentChange = false)
    {
        return $this->getRepository()->findOneBy(
            array(
                'benchmark' => $benchmark,
                'year' => $year,
                'percentile' => $percentile,
                'forPercentChange' => $forPercentChange
            )
        );
    }

    /**
     * @param $benchmark
     * @param $percentile
     * @param $forPercentChange
     * @return PercentileEntity[]
     */
    public function findByBenchmarkAndPercentile($benchmark, $percentile, $forPercentChange = false, $systemId = null)
    {
        return $this->getRepository()->findBy(
            array(
                'benchmark' => $benchmark,
                'percentile' => $percentile,
                'system' => $systemId,
                'forPercentChange' => $forPercentChange
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

    public function deleteByStudyAndYear($studyId, $year, $system = null, $forPercentChange = false)
    {
        $dql = 'DELETE Mrss\Entity\Percentile p
            WHERE p.year = ?1
            AND p.study = ?2
            AND p.forPercentChange = ?3 ';

        if ($system) {
            $dql .= ' AND p.system = ?4';
            //$dql .= ' AND p.system IS NOT NULL';
        } else {
            $dql .= ' AND p.system IS NULL';
        }

        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);
        $query->setParameter(3, $forPercentChange);

        if ($system) {
            $query->setParameter(4, $system->getId());
        }

        $query->execute();
    }
}
