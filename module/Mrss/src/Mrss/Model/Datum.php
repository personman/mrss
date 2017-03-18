<?php

namespace Mrss\Model;

use Mrss\Entity\Datum as DatumEntity;

/**
 * Class Percentile
 *
 * @package Mrss\Model
 */
class Datum extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Datum';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findBySubscriptionAndBenchmark($subscription, $benchmark)
    {
        return $this->getRepository()->findOneBy(
            array(
                'subscription' => $subscription,
                'benchmark' => $benchmark
            )
        );
    }

    public function findZeros($year)
    {
        /**
        select s.id, count(d.id) from data_values d
        inner join subscriptions s on d.subscription_id = s.id
        where s.year = 2017
        and d.floatValue = 0
        group by s.id;
         */

        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_ASSOC);
        $qb = $connection->createQueryBuilder();

        $qb->select(
            array(
                's.college_id',
                'count(d.id) as count'
            )
        );
        $qb->from('data_values', 'd');
        $qb->join('d', 'subscriptions', 's', 'd.subscription_id = s.id');
        $qb->andWhere("s.year = :year");
        $qb->andWhere("d.floatValue = 0");
        $qb->groupBy('s.id');

        $qb->setParameter('year', $year);

        try {
            $results = $qb->execute()->fetchAll();
        } catch (\Exception $e) {
            return array();
        }

        return $results;
    }

    public function save(DatumEntity $datum)
    {
        $this->getEntityManager()->persist($datum);

        // Flush elsewhere
    }
}
