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

    public function removeDuplicates()
    {
        $year = 2019;
        $connection = $this->getEntityManager()->getConnection();

        $sql = "select *, count(*) count
from data_values d
inner join subscriptions s on s.id = d.subscription_id
where s.year = $year
group by subscription_id, benchmark_id
having count > 1;";

        $statement = $connection->prepare($sql);
        $statement->execute();

        $results = $statement->fetchAll();

        echo 'Duplicate count: ';
        pr(count($results));

        foreach ($results as $result) {
            $this->handleDuplicate($result['subscription_id'], $result['benchmark_id']);
        }

        $this->getEntityManager()->flush();
        die('done');
    }

    protected function handleDuplicate($subscriptionId, $benchmarkId)
    {
        $duplicates = $this->getDuplicates($subscriptionId, $benchmarkId);

        $allNull = true;
        if (count($duplicates > 1)) {
            foreach ($duplicates as $datum) {
                $value = $datum->getValue();

                pr($value);
                if ($value !== null) {
                    $allNull = false;
                }
            }

            // If all are null, just keep the first one
            if ($allNull) {
                $firstDone = false;
                foreach ($duplicates as $datum) {
                    if (!$firstDone) {
                        // Keep this one. Trim the dbColumn (probable cause of issue)
                        $datum->setDbColumn(trim($datum->getDbColumn()));
                        $this->save($datum);
                        $firstDone = true;
                    } else {
                        // Delete it
                        $this->delete($datum);
                    }
                }
            } else {
                // Otherwise, keep the first one with a value
                $dupeRemoved = false;
                foreach ($duplicates as $datum) {
                    $value = $datum->getValue();
                    if (!$dupeRemoved && $value) {
                        // Keep this one. Trim the dbColumn (probable cause of issue)
                        $datum->setDbColumn(trim($datum->getDbColumn()));
                        $dupeRemoved = true;
                    } else {
                        // Delete it
                        $this->delete($datum);
                    }
                }
            }

            //pr($allNull);
        }
    }

    /**
     * @param $subscriptionId
     * @param $benchmarkId
     * @return DatumEntity[]
     */
    protected function getDuplicates($subscriptionId, $benchmarkId)
    {
        return $this->getRepository()->findBy(
            array(
                'subscription' => $subscriptionId,
                'benchmark' => $benchmarkId
            )
        );
    }

    public function delete(DatumEntity $datum)
    {
        $this->getEntityManager()->remove($datum);
    }
}
