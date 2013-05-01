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

    public function findOneByDbColumn($dbColumn)
    {
        return $this->getRepository()->findOneBy(array('dbColumn' => $dbColumn));
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

    public function save(BenchmarkEntity $benchmark)
    {
        $this->getEntityManager()->persist($benchmark);

        // Flush here or leave it to some other code?
    }

    public function getCompletionPercentages($dbColumn, $years)
    {
        if (empty($years)) {
            return array();
        }

        $years = implode(', ', $years);
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
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
