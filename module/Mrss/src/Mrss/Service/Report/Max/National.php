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
        $reportData['studentServicesPercentages'] = $this->getStudentServicesPercentages();
        $reportData['academicSupport'] = $this->getAcademicSupport();

        //pr($reportData);
        return $reportData;
    }

    public function getInstructionalActivityCosts()
    {
        /** @var ActivityReport\Instructional $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.instructional');
        return $report->getData($this->getObservation());
    }

    public function getStudentServicesCosts()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.ss');
        return $report->getData($this->getObservation());
    }

    public function getStudentServicesPercentages()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.ss.perc');
        return $report->getData($this->getObservation());
    }

    public function getAcademicSupport()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.as');
        return $report->getData($this->getObservation());
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
}
