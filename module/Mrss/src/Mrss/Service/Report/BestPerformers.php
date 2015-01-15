<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Benchmark;

class BestPerformers extends Report
{
    protected $percentileThreshold = 95;

    protected $year;

    public function getBenchmarks($year)
    {
        $this->year = $year;

        $this->getVariableSubstitution()->setStudyYear($year);

        $reportData = array();

        $study = $this->getStudy();

        $benchmarkGroups = $study->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            // Skip NCCBP form one
            if ($benchmarkGroup->getId() == 1) {
                continue;
            }

            $groupData = array(
                'name' => $benchmarkGroup->getName(),
                'timeframe' => $this->getVariableSubstitution()->substitute($benchmarkGroup->getTimeframe()),
                'benchmarks' => array()
            );

            //$benchmarks = $benchmarkGroup->getChildren($year, true, 'report', 'best-performers');
            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);

            foreach ($benchmarks as $benchmark) {
                if (!$benchmark->getIncludeInBestPerformer()) {
                    continue;
                }

                $label = $this->getVariableSubstitution()->substitute($benchmark->getDescriptiveReportLabel());
                $groupData['benchmarks'][$benchmark->getId()] = $label;
            }

            $reportData[] = $groupData;
        }

        return $reportData;
    }

    /**
     * Return the college names that had greater than the 85th percentile for this benchmark
     *
     * @param $benchmark
     * @return string
     */
    public function getBenchmarkData(Benchmark $benchmark)
    {
        $bestPerformers = $this->getPercentileRankModel()
            ->findBestPerformers($this->getStudy(), $benchmark, $this->year, $this->percentileThreshold);

        $collegeNames = array();
        foreach ($bestPerformers as $college) {
            $collegeNames[] = $college->getName() . ' (' . $college->getState() . ')';
        }

        // Present the colleges in a random order
        shuffle($collegeNames);

        $benchmarkData = array(
            'name' => $this->getVariableSubstitution()->substitute($benchmark->getReportLabel()),
            'bestPerformers' => $collegeNames
        );

        return $benchmarkData;
    }

    public function getBestPerformers($year, $benchmarkId)
    {
        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

        $bestPerformers = $this->getPercentileRankModel()
            ->findBestPerformers($this->getStudy(), $benchmark, $year, $this->percentileThreshold);

        $collegeNames = array();
        foreach ($bestPerformers as $college) {
            $collegeNames[] = $college->getName() . ' (' . $college->getState() . ')';
        }

        // Present the colleges in a random order
        shuffle($collegeNames);

        return $collegeNames;
    }
}
