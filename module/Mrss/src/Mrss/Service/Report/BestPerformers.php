<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\Benchmark;

class BestPerformers extends Report
{
    protected $percentileThreshold = 90;

    protected $year;

    public function getBenchmarks($subscription)
    {
        $this->year = $year = $subscription->getYear();

        $this->getVariableSubstitution()->setStudyYear($year);

        $reportData = array();

        $study = $this->getStudy();

        $benchmarkGroups = $study->getBenchmarkGroupsBySubscription($subscription);
        foreach ($benchmarkGroups as $benchmarkGroup) {
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

    public function getBestPerformers($year, $benchmarkId)
    {
        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

        $bestPerformers = $this->getPercentileRankModel()
            ->findBestPerformers($this->getStudy(), $benchmark, $year, $this->percentileThreshold);

        $collegeNames = array();
        foreach ($bestPerformers as $college) {
            $collegeNames[] = $college->getName() . ' (' . $college->getState() . ')';
        }

        // Present the colleges in alphabetical
        sort($collegeNames);

        return $collegeNames;
    }
}
