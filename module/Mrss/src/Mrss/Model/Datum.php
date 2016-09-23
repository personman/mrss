<?php

namespace Mrss\Model;

use \Mrss\Entity\Datum as DatumEntity;

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

    public function save(DatumEntity $datum)
    {
        $this->getEntityManager()->persist($datum);

        // Flush elsewhere
    }
}
