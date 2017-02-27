<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Percentile as PercentileEntity;
use Mrss\Entity\PercentileRank;
use Mrss\Entity\Benchmark;

class Percentile extends Report
{
    protected $stats = array(
            'benchmarks' => 0,
            'percentiles' => 0,
            'percentileRanks' => 0,
            'noData' => 0,
        );

    public function calculateForYear($year, $system = null)
    {
        die('calculateForYear is deprecated.');
        $baseMemory = memory_get_usage();

        $start = microtime(1);
        $this->debug($year);


        // Update any computed fields. Done in a separate step now. It took too long
        if (false && !$system) {
            $this->calculateAllComputedFields($year);
            $this->debugTimer('Just computed fields');

            return array();
        }

        $computeElapsed = microtime(1) - $start;

        $study = $this->getStudy();
        $percentileModel = $this->getPercentileModel();

        $this->clearPercentiles($year, $system);

        // Take note of some stats
        $this->stats['computeElapsed'] = $computeElapsed;

        // Loop over benchmarks
        $benchmarks = $study->getBenchmarksForYear($year);
        $this->debug(count($benchmarks));
        $this->debugTimer('prep done.');

        foreach ($benchmarks as $benchmark) {
            /** @var Benchmark $benchmark */
            $this->calculateForBenchmark($benchmark, $year, $system);

            // Flush periodically
            if ($this->stats['benchmarks'] % 50 == 0) {
                $i = $this->stats['benchmarks'];
                $percentileModel->getEntityManager()->flush();
            }
        }

        // Update the settings table with the calculation date
        $this->updateCalculationDate($year, $system);

        // Flush
        $percentileModel->getEntityManager()->flush();

        // Return some stats
        return $this->stats;
    }

    public function updateCalculationDate($year, $system = null)
    {
        $settingKey = $this->getReportCalculatedSettingKey($year, $system);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));
    }

    public function calculateForBenchmark(Benchmark $benchmark, $year, $system = null, $forPercentChange = false)
    {
        $study = $this->getStudy();

        $calculator = $this->getCalculator();
        $breakpoints = $this->getPercentileBreakpoints();
        $percentileModel = $this->getPercentileModel();
        $percentileRankModel = $this->getPercentileRankModel();

        // Get all data points for this benchmark
        // Can't just pull from observations. have to consider subscriptions, too
        $data = $this->collectDataForBenchmark($benchmark, $year, true, $system, $forPercentChange);

        if (empty($data)) {
            $this->stats['noData']++;
            return false;
        }

        $calculator->setData($data);

        // Percentiles
        foreach ($breakpoints as $breakpoint) {
            $value = $calculator->getValueForPercentile($breakpoint);

            $percentileEntity = new PercentileEntity;
            $percentileEntity->setStudy($study);
            $percentileEntity->setYear($year);
            $percentileEntity->setBenchmark($benchmark);
            $percentileEntity->setPercentile($breakpoint);
            $percentileEntity->setValue($value);
            $percentileEntity->setForPercentChange($forPercentChange);

            if ($system) {
                $percentileEntity->setSystem($system);
            }

            $percentileModel->save($percentileEntity);
            $this->stats['percentiles']++;
        }

        // Save the N (count) as a percentile
        $count = count($data);
        $percentileEntity = new PercentileEntity;
        $percentileEntity->setStudy($study);
        $percentileEntity->setYear($year);
        $percentileEntity->setBenchmark($benchmark);
        $percentileEntity->setPercentile('N');
        $percentileEntity->setValue($count);
        $percentileEntity->setForPercentChange($forPercentChange);
        if ($system) {
            $percentileEntity->setSystem($system);
        }

        $percentileModel->save($percentileEntity);

        // Percentile ranks
        foreach ($data as $collegeId => $datum) {
            $percentile = $calculator->getPercentileForValue($datum);

            $percentileRank = new PercentileRank;
            $percentileRank->setStudy($study);
            $percentileRank->setYear($year);
            $percentileRank->setBenchmark($benchmark);
            $percentileRank->setRank($percentile);
            $percentileRank->setForPercentChange($forPercentChange);

            if ($system) {
                $percentileRank->setSystem($system);
            }

            $college = $percentileRankModel->getEntityManager()
                ->getReference('Mrss\Entity\College', $collegeId);
            $percentileRank->setCollege($college);

            $percentileRankModel->save($percentileRank);
            $this->stats['percentileRanks']++;
        }

        $this->stats['benchmarks']++;

    }

    public function clearPercentiles($year, $system = null, $forPercentChange = false)
    {
        $percentileModel = $this->getPercentileModel();
        $percentileRankModel = $this->getPercentileRankModel();
        $study = $this->getStudy();

        // Clear the stored values
        $this->debugTimer('About to clear values');
        $percentileModel->deleteByStudyAndYear($study->getId(), $year, $system, $forPercentChange);
        $percentileRankModel->deleteByStudyAndYear($study->getId(), $year, $system, $forPercentChange);
        $this->debugTimer('cleared values');
    }

    /**
     * @param $year
     * @return array
     * @throws \Exception
     * @deprecated
     */
    public function calculateSystems($year)
    {
        throw new \Exception("calculateSystem is dprecated.");


        $statTotals = array(
            'benchmarks' => 0,
            'percentiles' => 0,
            'percentileRanks' => 0,
            'noData' => 0,
            'systems' => 0
        );

        $systems = $this->getSystemModel()->findAll();
        foreach ($systems as $system) {
            $stats = $this->calculateForYear($year, $system);

            $statTotals['systems']++;
            $statTotals['benchmarks'] += $stats['benchmarks'];
            $statTotals['percentiles'] += $stats['percentiles'];
            $statTotals['percentileRanks'] += $stats['percentileRanks'];
            $statTotals['noData'] += $stats['noData'];
        }

        return $statTotals;
    }
}
