<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Entity\Benchmark;
use Mrss\Entity\Percentile;
use Mrss\Entity\PercentileRank;
use Mrss\Entity\Observation;
use Mrss\Entity\PeerGroup;
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
     * @var \Mrss\Model\Benchmark
     */
    protected $benchmarkModel;

    /**
     * @var \Mrss\Model\College
     */
    protected $collegeModel;

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
        $percentileRankModel->deleteByStudyAndYear($study->getId(), $year);

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

            // Save the N (count) as a percentile
            $n = count($data);
            $percentileEntity = new Percentile;
            $percentileEntity->setStudy($study);
            $percentileEntity->setYear($year);
            $percentileEntity->setBenchmark($benchmark);
            $percentileEntity->setPercentile('N');
            $percentileEntity->setValue($n);

            $percentileModel->save($percentileEntity);

            // Percentile ranks
            foreach ($data as $collegeId => $datum) {
                $percentile = $calculator->getPercentileForValue($datum);

                if (false && $collegeId == 101 && $benchmark->getId() == 1) {
                    var_dump($data);
                    var_dump($datum);
                    var_dump($percentile);
                    die;
                }

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
     * @return array
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
                if ($this->isBenchmarkExcludeFromReport($benchmark)) {
                    continue;
                }

                $benchmarkData = array(
                    'benchmark' => $benchmark->getName(),
                );

                $percentiles = $this->getPercentileModel()
                    ->findByBenchmarkAndYear($benchmark, $year);

                $percentileData = array();
                foreach ($percentiles as $percentile) {
                    $percentileData[$percentile->getPercentile()] =
                        $percentile->getValue();
                }

                if (!empty($percentileData['N'])) {
                    $benchmarkData['N'] = $percentileData['N'];
                    unset($percentileData['N']);
                } else {
                    $benchmarkData['N'] = '';
                }


                $benchmarkData['percentiles'] = $percentileData;

                $benchmarkData['reported'] = $observation->get(
                    $benchmark->getDbColumn()
                );

                $percentileRank = $this->getPercentileRankModel()
                    ->findOneByCollegeBenchmarkAndYear(
                        $observation->getCollege(),
                        $benchmark,
                        $year
                    );

                if (!empty($percentileRank)) {
                    $benchmarkData['percentile_rank_id'] = $percentileRank->getId();
                    $benchmarkData['percentile_rank'] = $percentileRank->getRank();
                } else {
                    $benchmarkData['percentile_rank_id'] = '';
                    $benchmarkData['percentile_rank'] = '';
                }

                $groupData['benchmarks'][] = $benchmarkData;

            }

            $reportData[] = $groupData;
        }

        //echo '<pre>' . print_r($reportData, 1) . '</pre>';
        return $reportData;
    }

    public function getPercentileBreakpoints()
    {
        return array(10, 25, 50, 75, 90);
    }

    public function getPercentileBreakPointLabels()
    {
        $breakpoints = $this->getPercentileBreakpoints();
        $labels = array();
        foreach ($breakpoints as $breakpoint) {
            if ($breakpoint == 50) {
                $label = 'Mdn';
            } else {
                $label = $this->getOrdinal($breakpoint);
            }

            $labels[] = $label;
        }

        return $labels;
    }

    public function getBenchmarksToExcludeFromReport()
    {
        return array(
            'institutional_demographics_campus_environment',
            'institutional_demographics_staff_unionized',
            'institutional_demographics_faculty_unionized',
        );
    }

    public function isBenchmarkExcludeFromReport(Benchmark $benchmark)
    {
        $toExclude = $this->getBenchmarksToExcludeFromReport();

        return in_array($benchmark->getDbColumn(), $toExclude);
    }

    public function getOrdinal($number)
    {
        // We don't want to show 0 or 99, so use > or < for those
        if ($number < 1) {
            $html = '<1<sup>st</sup>';
        } elseif ($number > 99) {
            $html = '>99<sup>th</sup>';
        } else {
            $rounded = round($number);

            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
            if (($rounded % 100) >= 11 && ($rounded % 100) <= 13) {
                $abbreviation = 'th';
            } else {
                $abbreviation = $ends[$rounded % 10];
            }

            $html = "$rounded<sup>$abbreviation</sup>";
        }

        return $html;
    }

    public function getPeerReport(PeerGroup $peerGroup)
    {
        $minPeers = 2;

        $report = array();
        
        $year = $peerGroup->getYear();
        $benchmarks = $peerGroup->getBenchmarks();
        $colleges = $peerGroup->getPeers();
        $colleges[] = $peerGroup->getCollege()->getId();

        $observations = array();
        $collegeEntities = array();

        // Fetch the colleges and their observation data for the year
        foreach ($colleges as $collegeId) {
            $college = $this->getCollegeModel()
                ->find($collegeId);

            $collegeEntities[$collegeId] = $college;
            $observations[$collegeId] = $college->getObservationForYear($year);
        }


        foreach ($benchmarks as $benchmarkId) {
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

            // Build the report data
            $data = array();
            foreach ($collegeEntities as $college) {
                $observation = $observations[$college->getId()];
                $value = $observation->get($benchmark->getDbColumn());

                if ($value !== null) {
                    $data[$college->getId()] = $value;
                }
            }

            if (count($data) <= $minPeers) {
                continue;
            }

            $data = $this->sortAndLabelPeerData($data, $peerGroup->getCollege());

            $reportSection = array(
                'benchmark' => $benchmark->getName(),
                'data' => $data
            );

            $report[] = $reportSection;
        }


        return $report;
    }

    public function sortAndLabelPeerData($data, $currentCollege)
    {
        arsort($data);
        $dataWithLabels = array();

        $i = 1;
        foreach ($data as $collegeId => $value) {
            if ($collegeId == $currentCollege->getId()) {
                $label = $currentCollege->getName();
            } else {
                $label = $this->numberToLetter($i);
                $i++;
            }

            $dataWithLabels[$label] = $value;
        }

        return $dataWithLabels;
    }

    /**
     * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
     *
     * @access	public
     * @param	int	number to convert
     * @param	bool	upper case the letter on return?
     * @return	string	letters from number input
     */

    function numberToLetter($num, $uppercase = true)
    {
        $num -= 1;

        $letter = 	chr(($num % 26) + 97);
        $letter .= 	(floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return 		($uppercase ? strtoupper($letter) : $letter);
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

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
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

    /**
     * @return \Mrss\Model\PercentileRank
     */
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
