<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Mrss\Entity\Subscription;
use Mrss\Entity\Datum;
use Mrss\Entity\Study;

/**
 * Class ObservationDataMigration
 *
 * Move data points from old observations table to new Datum entities
 *
 * @package Mrss\Service
 */
class ObservationDataMigration
{
    protected $datumModel;

    protected $study;

    public function copyAllSubscriptions()
    {
        foreach ($this->getStudy()->getSubscriptions() as $subscription) {
            $this->copySubscription($subscription);
        }
    }

    public function copySubscription(Subscription $subscription)
    {
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $this->copySubscriptionBenchmark($subscription, $benchmark);
        }

        $this->getDatumModel()->getEntityManager()->flush();
    }

    public function copySubscriptionBenchmark(Subscription $subscription, Benchmark $benchmark)
    {
        $observation = $subscription->getObservation();

        $datum = $this->getOrCreateDatum($subscription, $benchmark);
        $value = $observation->get($benchmark->getDbColumn());
        $datum->setValue($value);

        $this->getDatumModel()->save($datum);
    }

    public function getOrCreateDatum(Subscription $subscription, Benchmark $benchmark)
    {
        $datum = $this->getDatumModel()->findBySubscriptionAndBenchmark($subscription, $benchmark);

        if (!$datum) {
            $datum = new Datum;
            $datum->setBenchmark($benchmark);
            $datum->setSubscription($subscription);
        }

        return $datum;
    }

    /**
     * @return \Mrss\Model\Datum
     */
    public function getDatumModel()
    {
        return $this->datumModel;
    }

    /**
     * @param mixed $datumModel
     * @return ObservationDataMigration
     */
    public function setDatumModel($datumModel)
    {
        $this->datumModel = $datumModel;
        return $this;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return Study
     */
    public function getStudy()
    {
        return $this->study;
    }
}
