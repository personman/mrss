<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Entity\Benchmark;
use Mrss\Entity\Percentile;
use Mrss\Entity\PercentileRank;
use Mrss\Entity\Observation;
use Mrss\Service\Report\Calculator;

class Report
{
    /**
     * @var Study
     */
    protected $study;

    /**
     * @var Calculator
     */
    protected $calculator;

    /**
     * @var \Mrss\Model\Subscription
     */
    protected $subscriptionModel;

    /**
     * @var \Mrss\Model\Percentile
     */
    protected $percentileModel;

    /**
     * @var \Mrss\Model\PercentileRank
     */
    protected $percentileRankModel;

    /**
     * @var \Mrss\Model\Setting
     */
    protected $settingModel;

    public function getYearsWithSubscriptions()
    {
        $years = $this->getSubscriptionModel()
            ->getYearsWithSubscriptions($this->getStudy());

        // Also show the date the report was calculated
        $yearsWithCalculationDates = array();
        foreach ($years as $year) {
            $key = $this->getSettingKey($year);
            $yearsWithCalculationDates[$year] = $this->getSettingModel()
                ->getValueForIdentifier($key);
        }

        return $yearsWithCalculationDates;
    }

    public function calculateForYear($year)
    {
        $study = $this->getStudy();
        $calculator = $this->getCalculator();
        $breakpoints = $this->getPercentileBreakpoints();
        $percentileModel = $this->getPercentileModel();
        $percentileRankModel = $this->getPercentileRankModel();

        // Clear the stored values
        $percentileModel->deleteByStudyAndYear($study->getId(), $year);

        // Take note of some stats
        $stats = array(
            'benchmarks' => 0,
            'percentiles' => 0,
            'percentileRanks' => 0
        );

        // Loop over benchmarks
        foreach ($study->getBenchmarksForYear($year) as $benchmark) {
            // Get all data points for this benchmark
            // Can't just pull from observations. have to consider subscriptions, too
            $data = $this->collectDataForBenchmark($benchmark, $year);

            if (empty($data)) {
                continue;
            }

            $calculator->setData($data);

            // Percentiles
            foreach ($breakpoints as $breakpoint) {
                $value = $calculator->getValueForPercentile($breakpoint);

                $percentileEntity = new Percentile;
                $percentileEntity->setStudy($study);
                $percentileEntity->setYear($year);
                $percentileEntity->setBenchmark($benchmark);
                $percentileEntity->setPercentile($breakpoint);
                $percentileEntity->setValue($value);

                $percentileModel->save($percentileEntity);
                $stats['percentiles']++;
            }

            // Percentile ranks
            foreach ($data as $collegeId => $datum) {
                $percentile = $calculator->getPercentileForValue($datum);

                $percentileRank = new PercentileRank;
                $percentileRank->setStudy($study);
                $percentileRank->setYear($year);
                $percentileRank->setBenchmark($benchmark);
                $percentileRank->setRank($percentile);

                $college = $percentileRankModel->getEntityManager()
                    ->getReference('Mrss\Entity\College', $collegeId);
                $percentileRank->setCollege($college);

                $percentileRankModel->save($percentileRank);
                $stats['percentileRanks']++;
            }

            $stats['benchmarks']++;
        }

        // Update the settings table with the calculation date
        $settingKey = $this->getSettingKey($year);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));

        // Flush
        $percentileModel->getEntityManager()->flush();

        // Return some stats
        return $stats;
    }

    /**
     * Build a unique key for the year and study
     *
     * @param $year
     * @return string
     */
    public function getSettingKey($year)
    {
        $studyId = $this->getStudy()->getId();

        $key = "report_calculated_{$studyId}_$year";

        return $key;
    }

    public function collectDataForBenchmark(Benchmark $benchmark, $year)
    {
        $subscriptions = $this->getSubscriptionModel()
            ->findByStudyAndYear($this->getStudy()->getId(), $year);

        $data = array();
        /** @var $subscription /Mrss/Entity/Subscription */
        foreach ($subscriptions as $subscription) {
            /** @var /Mrss/Entity/Observation $observation */
            $observation = $subscription->getObservation();
            $dbColumn = $benchmark->getDbColumn();
            $value = $observation->get($dbColumn);
            $collegeId = $subscription->getCollege()->getId();

            // Leave out null values
            if ($value !== null) {
                $data[$collegeId] = $value;
            }
        }

        return $data;
    }

    /**
     * Get the basic national percentile report in the form of nested
     * arrays, suitable for building an html, csv, or excel report.
     *
     * @param Observation $observation
     */
    public function getNationalReportData(Observation $observation)
    {
        $year = $observation->getYear();
        $reportData = array();

        $study = $this->getStudy();

        $benchmarkGroups = $study->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            $groupData = array(
                'benchmarkGroup' => $benchmarkGroup->getName(),
                'benchmarks' => array()
            );

            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
            foreach ($benchmarks as $benchmark) {
                $bencmarkData = array(
                    'benchmark' => $benchmark->getName(),
                );

                $percentiles = $this->getPercentileModel()
                    ->findByBenchmarkAndYear($benchmark, $year);

                $percentileData = array();
                foreach ($percentiles as $percentile) {
                    $percentileData[$percentile->getPercentile()] =
                        $percentile->getValue();
                }

                $bencmarkData['percentiles'] = $percentileData;

                $groupData['benchmarks'][] = $bencmarkData;

                // @todo: how to pull the pre-calc'd percentiles
            }

            $reportData[] = $groupData;
        }

        echo '<pre>' . print_r($reportData, 1) . '</pre>';
    }

    public function getPercentileBreakpoints()
    {
        return array(10, 25, 50, 75, 90);
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setPercentileModel($model)
    {
        $this->percentileModel = $model;

        return $this;
    }

    public function getPercentileModel()
    {
        return $this->percentileModel;
    }

    public function setPercentileRankModel($model)
    {
        $this->percentileRankModel = $model;

        return $this;
    }

    public function getPercentileRankModel()
    {
        return $this->percentileRankModel;
    }

    public function setSettingModel($model)
    {
        $this->settingModel = $model;

        return $this;
    }

    public function getSettingModel()
    {
        return $this->settingModel;
    }

    public function setCalculator(Calculator $calculator)
    {
        $this->calculator = $calculator;

        return $this;
    }

    public function getCalculator()
    {
        return $this->calculator;
    }
}
