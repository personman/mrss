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
        }

        return true;
    }

    public function copySubscriptionBenchmark(Subscription $subscription, Benchmark $benchmark)
    {
        $observation = $subscription->getObservation();

        $datum = $this->getOrCreateDatum($subscription, $benchmark);
        $value = $observation->getOld($benchmark->getDbColumn());
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

    public function check($minId = 0)
    {
        $count = 0;
        $mistakes = array();
        foreach ($this->getStudy()->getSubscriptions() as $subscription) {
            if ($subscription->getId() < $minId) {
                continue;
            }

            foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
                $oldValue = $subscription->getObservation()->getOld($benchmark->getDbColumn());
                $newValue = $subscription->getValue($benchmark);

                if ($benchmark->isNumber()) {
                    $round = 5;
                    $oldValue = round(floatval($oldValue), $round);
                    $newValue = round(floatval($newValue), $round);
                }

                if ($oldValue != $newValue) {
                    $mistakes[] = array(
                        'sub' => $subscription->getId(),
                        'benchmark' => $benchmark->getId(),
                        'old' => $oldValue,
                        'new' => $newValue
                    );

                    prd($mistakes);
                }
            }

            $count++;

            pr($subscription->getId());
            if ($count >= 10) {
                if (empty($mistakes)) {
                    echo 'Ok so far.';

                    $protocol = 'http://';
                    $host = $protocol . $_SERVER['HTTP_HOST'];
                    //pr($_SERVER);
                    $url = $host . '/admin/check-migration/' . $subscription->getId();
                    pr($url);
                    pr($count);
                    echo "<script>location.href = '$url';</script>";
                    die;
                } else {
                    pr($count);
                    prd($mistakes);
                }
            }
        }

        echo 'all done';
        pr($mistakes);

        die('blah');
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
