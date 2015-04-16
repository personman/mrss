<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report\Max;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;

class National extends Max
{
    public function getData(Observation $observation)
    {
        $this->setObservation($observation);

        $reportData = array();

        $reportData['instructional'] = $this->getInstructionalActivityCosts();
        $reportData['studentServices'] = $this->getStudentServicesCosts();

        //pr($reportData);
        return $reportData;
    }

    public function getInstructionalActivityCosts()
    {
        // Looks like form 2 doesn't have the cost per fte field
        // And need to confirm that aggregating form 2 values makes mathematical sense
        $activities = $this->getActivities();

        return null;
    }

    public function getStudentServicesCosts()
    {
        $studentServicesData = array();

        $activities = $this->getStudentServicesCostsFields();

        foreach ($activities as $label => $fields) {
            $costPerFteField = $fields[0];

            $benchmark = $this->getBenchmark($costPerFteField);

            //$value = $this->getObservation()->get($costPerFteField);

            /*$benchmarkData = array(
                'label' => $label,
                'reported' => $value
            );*/



            $benchmarkData = $this->getBenchmarkData($benchmark);
            $benchmarkData['benchmark'] = $label;
            $benchmarkData['details'] = $this->getDetails($costPerFteField);

            $studentServicesData[] = $benchmarkData;
        }

        return $studentServicesData;
    }

    // @todo: move this up to base report class
    public function getBenchmarkData(Benchmark $benchmark)
    {
        $benchmarkData = array(
            'benchmark' => $this->getVariableSubstitution()->substitute($benchmark->getReportLabel()),
            'dbColumn' => $benchmark->getDbColumn()
        );

        $year = $this->getObservation()->getYear();
        $percentiles = $this->getPercentileModel()
            ->findByBenchmarkAndYear($benchmark, $year, $this->getSystem());

        $percentileData = array();
        foreach ($percentiles as $percentile) {
            $percentileData[$percentile->getPercentile()] =
                $percentile->getValue();
        }

        // Pad the array if it's empty
        if (empty($percentileData)) {
            $percentileData = array(null, null, null, null, null);
        }

        if (!empty($percentileData['N'])) {
            $benchmarkData['N'] = $percentileData['N'];
            unset($percentileData['N']);
        } else {
            $benchmarkData['N'] = '';
        }


        $benchmarkData['percentiles'] = $percentileData;

        $benchmarkData['reported'] = $this->getObservation()->get(
            $benchmark->getDbColumn()
        );

        $benchmarkData['reported_decimal_places'] = $this
            ->getDecimalPlaces($benchmark);

        $percentileRank = $this->getPercentileRankModel()
            ->findOneByCollegeBenchmarkAndYear(
                $this->getObservation()->getCollege(),
                $benchmark,
                $year,
                $this->getSystem()
            );

        if (!empty($percentileRank)) {
            $benchmarkData['percentile_rank_id'] = $percentileRank->getId();
            $benchmarkData['percentile_rank'] = $percentileRank->getRank();

            // Show - rather than 0 percentile
            if ($benchmarkData['reported'] == 0) {
                $benchmarkData['percentile_rank'] = '-';
            }

        } else {
            $benchmarkData['percentile_rank_id'] = '';
            $benchmarkData['percentile_rank'] = '';
        }

        // Data labels
        $prefix = $suffix = '';
        if ($benchmark->isPercent()) {
            $suffix = '%';
        } elseif ($benchmark->isDollars()) {
            $prefix = '$';
        }

        $benchmarkData['prefix'] = $prefix;
        $benchmarkData['suffix'] = $suffix;

        // Timeframe
        $benchmarkData['timeframe'] = $this->getVariableSubstitution()->substitute($benchmark->getTimeframe());

        // Chart
        $chartConfig = array(
            'dbColumn' => $benchmark->getDbColumn(),
            'decimal_places' => $this->getDecimalPlaces($benchmark)
        );

        $benchmarkData['chart'] = $this->getPercentileBarChart(
            $chartConfig,
            $this->getObservation()
        );

        $benchmarkData['description'] = $this->getVariableSubstitution()
            ->substitute($benchmark->getReportDescription(1));


        if ($benchmarkData['percentile_rank'] === '-') {
            $benchmarkData['do_not_format_rank'] = true;
        } elseif ($benchmarkData['percentile_rank'] < 1) {
            $rank = '<1%';
            $benchmarkData['percentile_rank'] = $rank;
            $benchmarkData['do_not_format_rank'] = true;
        } elseif ($benchmarkData['percentile_rank'] > 99) {
            $rank = '>99%';
            $benchmarkData['percentile_rank'] = $rank;
            $benchmarkData['do_not_format_rank'] = true;
        }

        return $benchmarkData;
    }

    protected function getSystem()
    {
        return null;
    }

    protected function getDetails($topLevelDbColumn)
    {
        $activity = $this->extractBaseActivityFromDbColumn($topLevelDbColumn);

        $dbColumns = $this->getDetailColumnsForActivity($activity);
        $details = array();

        foreach ($dbColumns as $dbColumn => $label) {
            $benchmark = $this->getBenchmark($dbColumn);

            if ($benchmark) {
                $benchmarkData = $this->getBenchmarkData($benchmark);

                // Set the label
                $benchmarkData['benchmark'] = $label;

                $details[] = $benchmarkData;
            } else {
                echo "<p>Unable to find benchmark for $dbColumn " .
                    "(top level: $topLevelDbColumn).</p>";
            }

        }


        return $details;
    }

    protected function getDetailColumnsForActivity($activity)
    {
        $fields = array(
            "ss_{$activity}_cost_per_fte_emp" => "Average Salary and Benefits",
            "ss_{$activity}_students_per_fte_emp" => "FTE Students per Staff Person"
        );

        return $fields;
    }


    protected function extractBaseActivityFromDbColumn($dbColumn)
    {
        $activities = array(
            'admissions',
            'recruitment',
            'advising',
            'counseling',
            'career',
            'financial_aid',
            'registrar',
            'tutoring',
            'testing',
            'cocurricular',
            'disabserv',
            'vetserv'
        );

        foreach ($activities as $activity) {
            if (strstr($dbColumn, $activity)) {
                return $activity;
            }
        }
    }
}
