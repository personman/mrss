<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Percentile as PercentileEntity;
use Mrss\Entity\PercentileRank;

class Percentile extends Report
{
    public function calculateForYear($year, $system = null)
    {
        $baseMemory = memory_get_usage();

        $start = microtime(1);
        $this->debug($year);


        // Update any computed fields
        if (!$system) {
            $this->calculateAllComputedFields($year);
            $this->debugTimer('Just computed fields');
        }

        $study = $this->getStudy();

        $calculator = $this->getCalculator();
        $breakpoints = $this->getPercentileBreakpoints();
        $percentileModel = $this->getPercentileModel();
        $percentileRankModel = $this->getPercentileRankModel();

        // Clear the stored values
        $this->debugTimer('About to clear values');
        $percentileModel->deleteByStudyAndYear($study->getId(), $year, $system);
        $percentileRankModel->deleteByStudyAndYear($study->getId(), $year, $system);
        $this->debugTimer('cleared values');

        // Take note of some stats
        $stats = array(
            'benchmarks' => 0,
            'percentiles' => 0,
            'percentileRanks' => 0,
            'noData' => 0
        );

        // Loop over benchmarks
        $benchmarks = $study->getBenchmarksForYear($year);
        $this->debug(count($benchmarks));
        $this->debugTimer('prep done.');

        foreach ($benchmarks as $benchmark) {
            /** @var Benchmark $benchmark */

            // Get all data points for this benchmark
            // Can't just pull from observations. have to consider subscriptions, too
            $data = $this->collectDataForBenchmark($benchmark, $year, true, $system);

            if (empty($data)) {
                $stats['noData']++;
                continue;
            }

            // Debug
            //prd($data);

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

                if ($system) {
                    $percentileEntity->setSystem($system);
                }

                $percentileModel->save($percentileEntity);
                $stats['percentiles']++;
            }

            // Save the N (count) as a percentile
            $n = count($data);
            $percentileEntity = new PercentileEntity;
            $percentileEntity->setStudy($study);
            $percentileEntity->setYear($year);
            $percentileEntity->setBenchmark($benchmark);
            $percentileEntity->setPercentile('N');
            $percentileEntity->setValue($n);
            if ($system) {
                $percentileEntity->setSystem($system);
            }

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

                if ($system) {
                    $percentileRank->setSystem($system);
                }

                $college = $percentileRankModel->getEntityManager()
                    ->getReference('Mrss\Entity\College', $collegeId);
                $percentileRank->setCollege($college);

                $percentileRankModel->save($percentileRank);
                $stats['percentileRanks']++;
            }

            $stats['benchmarks']++;

            // Flush periodically
            if ($stats['benchmarks'] % 50 == 0) {
                $i = $stats['benchmarks'];
                //pr($stats['benchmarks']);
                //echo sprintf( '%8d: ', $i ), memory_get_usage() - $baseMemory, "\n<br>";
                $percentileModel->getEntityManager()->flush();
                //echo sprintf( '%8d: ', $i ), memory_get_usage() - $baseMemory, "\n<br>";
            }
        }

        // Update the settings table with the calculation date
        $settingKey = $this->getReportCalculatedSettingKey($year, $system);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));

        // Flush
        $percentileModel->getEntityManager()->flush();

        // Return some stats
        return $stats;
    }

    public function calculateSystems($year)
    {
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
