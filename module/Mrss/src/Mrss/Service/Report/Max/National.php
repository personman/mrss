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
        $reportData['academicSupport'] = $this->getAcademicSupport();

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

            $benchmarkData = $this->getBenchmarkData($benchmark);
            $benchmarkData['benchmark'] = $label;
            $benchmarkData['details'] = $this->getDetails($costPerFteField);

            $studentServicesData[] = $benchmarkData;
        }

        return $studentServicesData;
    }

    public function getAcademicSupport()
    {
        $academicSupportData = array();

        $activities = $this->getAcademicSupportActivities();

        foreach ($activities as $activity => $label) {
            $field = "as_{$activity}_cost_per_fte_student";

            $benchmark = $this->getBenchmark($field);

            $benchmarkData = $this->getBenchmarkData($benchmark);
            $benchmarkData['benchmark'] = $label;
            $benchmarkData['details'] = $this->getDetails($activity);

            $academicSupportData[] = $benchmarkData;
        }

        //pr($academicSupportData);
        return $academicSupportData;
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

            if (is_object($benchmark)) {
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
        $academicSupportActivities = array_keys($this->getAcademicSupportActivities());

        if (in_array($activity, $this->getStudentServicesActivities())) {
            $fields = array(
                "ss_{$activity}_cost_per_fte_emp" => "Average Salary and Benefits",
                "ss_{$activity}_students_per_fte_emp" => "FTE Students per Staff Person",
                "ss_{$activity}_percent_salaries" => "% of Costs for Salaries and Benefits",
                "ss_{$activity}_percent_o_cost" => "% of Costs for Non-labor Operating Costs"
                // @todo: confirm equations are right and add benchmarks for contract costs
            );
        } elseif (in_array($activity, $academicSupportActivities)) {
            $fields = array(
                "as_{$activity}_cost_per_fte_emp" => "Average Salary and Benefits",
                "as_fte_students_per_{$activity}_fte_emp" => "FTE Students per Staff Person",
                "as_{$activity}_percent_salaries" => "% of Costs for Salaries and Benefits",
                "as_{$activity}_percent_o_cost" => "% of Costs for Non-labor Operating Costs"
                // @todo: confirm equations are right and add benchmarks for contract costs
            );
        } else {
            $fields = array();
        }

        return $fields;
    }


    protected function extractBaseActivityFromDbColumn($dbColumn)
    {
        $activities = $this->getStudentServicesActivities();
        $asActivities = array_keys($this->getAcademicSupportActivities());
        $activities = array_merge($activities, $asActivities);

        foreach ($activities as $activity) {
            if (strstr($dbColumn, $activity)) {
                return $activity;
            }
        }
    }

    protected function getStudentServicesActivities()
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

        return $activities;
    }

    protected function getAcademicSupportActivities()
    {
        return array(
            'tech' => 'Instructional Technology',
            'library' => 'Library Services',
            'experiential' => 'Experiential Education'
        );
    }
}
