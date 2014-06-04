<?php

namespace Mrss\Model;

use \Mrss\Entity\Benchmark  as BenchmarkEntity;
use Zend\Debug\Debug;

/**
 * Class Benchmark
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Benchmark extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Benchmark';

    /**
     * @param $dbColumn
     * @return null|BenchmarkEntity
     */
    public function findOneByDbColumn($dbColumn)
    {
        return $this->getRepository()->findOneBy(array('dbColumn' => $dbColumn));
    }

    public function findOneByDbColumnAndGroup($dbColumn, $benchmarkGroup)
    {
        // First try without the dbColumn prefix
        $benchmark = $this->getRepository()->findOneBy(
            array(
                'dbColumn' => $dbColumn,
                'benchmarkGroup' => $benchmarkGroup
            )
        );

        if (empty($benchmark)) {
            // If that returns nothing, try with the benchmark group prefix on the
            // dbColumn
            $dbColumn = $benchmarkGroup->getShortName() . '_' . $dbColumn;

            $benchmark = $this->getRepository()->findOneBy(
                array(
                    'dbColumn' => $dbColumn,
                    'benchmarkGroup' => $benchmarkGroup
                )
            );
        }

        return $benchmark;
    }

    public function findOneByDbColumnAndStudy($dbColumn, $studyId)
    {
        /** @var \Mrss\Entity\Benchmark[] $benchmarks */
        $benchmarks = $this->getRepository()->findBy(array('dbColumn' => $dbColumn));

        foreach ($benchmarks as $benchmark) {
            if ($benchmark->getBenchmarkGroup()->getStudy()->getId() == $studyId) {
                return $benchmark;
            }
        }
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmarks, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('sequence' => 'ASC'));
        return $c;
    }

    /**
     * @return \Mrss\Entity\Benchmark[]
     */
    public function findComputed()
    {
        return $this->getRepository()->findBy(array('computed' => true));
    }

    public function save(BenchmarkEntity $benchmark)
    {
        // Confirm that the sequence is set
        if ($benchmark->getSequence() === null) {
            $max = $this->getMaxSequence();
            $newMax = $max + 1;
            $benchmark->setSequence($newMax);
        }

        $this->getEntityManager()->persist($benchmark);

        // Flush here or leave it to some other code?
    }

    public function getMaxSequence()
    {
        $lastBenchmark = $this->getRepository()->findOneBy(
            array(),
            array('sequence' => 'DESC')
        );

        if (!empty($lastBenchmark)) {
            $max = $lastBenchmark->getSequence();
        } else {
            $max = 1;
        }

        return $max;
    }


    public function getCompletionPercentages($dbColumn, $years)
    {
        if (empty($years)) {
            return array();
        }

        $years = implode(', ', $years);
        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_ASSOC);
        $qb = $connection->createQueryBuilder();

        $qb->select(
            array(
                'year',
                'SUM(IF(' . $dbColumn . ' IS NULL, 0, 1)) / COUNT(id) * 100 AS
                percentage'
            )
        );
        $qb->from('observations', 'o');
        $qb->where("year IN($years)");
        $qb->groupBy('year');

        try {
            $results = $qb->execute()->fetchAll();
        } catch (\Exception $e) {
            return array();
        }

        $completionPercentages = array();
        foreach ($results as $cp) {
            $completionPercentages[$cp['year']] = $cp['percentage'];
        }

        return $completionPercentages;
    }
}
