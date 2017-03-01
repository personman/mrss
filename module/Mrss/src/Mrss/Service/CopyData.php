<?php

namespace Mrss\Service;

/**
 * Class CopyData
 *
 * For the given years and benchmark ids, loop over subscriptions and copy relevant data from the source benchmarks
 * to the destination benchmarks. If the destination already has a value, don't overwrite it.
 *
 * @package Mrss\Service
 */
class CopyData
{
    protected $subscriptionModel;

    protected $benchmarkModel;

    protected $study;

    public function copy($benchmarks, $years)
    {
        foreach ($years as $year) {
            $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear($this->getStudy()->getId(), $year);

            foreach ($subscriptions as $subscription) {
                $allData = $subscription->getAllData('id');

                // $source and $destination are benchmark ids
                foreach ($benchmarks as $source => $destination) {
                    if (!isset($allData[$source])) {
                        continue;
                    }


                    // If the destination isn't empty, don't bother
                    $destinationValue = $allData[$destination];
                    $sourceValue = $allData[$source];

                    if (empty($destinationValue) || $destinationValue == '') {
                        if (!empty($sourceValue) && $sourceValue != '') {
                            $benchmark = $this->getBenchmarkModel()->find($destination);
                            $datum = $subscription->getDatum($benchmark);
                            $datum->setValue($sourceValue);

                            $this->getSubscriptionModel()->save($subscription);
                        }
                        pr($destinationValue);
                        pr($sourceValue);

                        echo '<hr>';
                    }


                    //if ($subscription->getDatum($destination))
                }
            }

            pr(count($subscriptions));
        }

        $this->getSubscriptionModel()->getEntityManager()->flush();
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @return mixed
     */
    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    /**
     * @param mixed $benchmarkModel
     * @return CopyData
     */
    public function setBenchmarkModel($benchmarkModel)
    {
        $this->benchmarkModel = $benchmarkModel;
        return $this;
    }


}
