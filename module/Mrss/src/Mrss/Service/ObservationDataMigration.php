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
        $year = 2016;

        $count = 0;
        foreach ($this->getStudy()->getSubscriptionsForYear($year) as $subscription) {
            //echo 'Copying 2016 only';
            /*if ($subscription->getYear() != 2016) {
                continue;
            }*/
            if ($subscription->getCollege()->getId() != 10) {
                continue;
            }

            $this->copySubscription($subscription);
            $count++;
        }

        return $count;
    }

    public function copySubscription(Subscription $subscription)
    {
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $this->copySubscriptionBenchmark($subscription, $benchmark);
        }

        $this->getDatumModel()->getEntityManager()->flush();
    }

    public function copyObservation(Observation $observation)
    {
        $subs = $observation->getSubscriptions();

        if ($sub = $subs[0]) {
            $this->copySubscription($sub);
            return true;
        }
    }

    public function copySubscriptionBenchmark(Subscription $subscription, Benchmark $benchmark)
    {
        $observation = $subscription->getObservation();

        $datum = $this->getOrCreateDatum($subscription, $benchmark);
        $value = $observation->get($benchmark->getDbColumn());
        $datum->setValue($value);

        $this->getDatumModel()->save($datum);

        /*if ($benchmark->getDbColumn() == 'ft_average_no_rank_salary') {
            pr($observation->get('ft_average_no_rank_salary'));
            pr($observation->get($benchmark->getDbColumn()));
            pr($value);
        }*/
    }

    public function getOrCreateDatum(Subscription $subscription, Benchmark $benchmark)
    {
        $datum = $this->getDatumModel()->findBySubscriptionAndBenchmark($subscription, $benchmark);

        if (!$datum) {
            $datum = new Datum;
            $datum->setBenchmark($benchmark);
            $datum->setSubscription($subscription);
            $datum->setDbColumn($benchmark->getDbColumn());
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
