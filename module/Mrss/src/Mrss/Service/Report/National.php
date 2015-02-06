<?php

namespace Mrss\Service\Report;

use Mrss\Entity\Observation;
use Mrss\Entity\Study;
use Mrss\Entity\Benchmark;
use Mrss\Entity\System;
use Mrss\Service\Report;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_Shared_Font;

class National extends Report
{
    /**
     * @var \Mrss\Model\Percentile
     */
    protected $percentileModel;

    /**
     * @var \Mrss\Model\PercentileRank
     */
    protected $percentileRankModel;

    protected $system;


    public function getData(Observation $observation, $system = null)
    {
        $this->setObservation($observation);
        $this->setSystem($system);
        $year = $observation->getYear();
        $this->getVariableSubstitution()->setStudyYear($year);

        $reportData = array();

        $study = $this->getStudy();

        $benchmarkGroups = $study->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            $groupData = array(
                'benchmarkGroup' => $benchmarkGroup->getName(),
                'timeframe' => $this->getVariableSubstitution()->substitute($benchmarkGroup->getTimeframe()),
                'url' => $benchmarkGroup->getUrl(),
                'benchmarks' => array()
            );
            $benchmarks = $benchmarkGroup->getChildren($year, true, 'report', 'report');

            foreach ($benchmarks as $benchmark) {
                if (get_class($benchmark) == 'Mrss\Entity\BenchmarkHeading') {
                    /** @var \Mrss\Entity\BenchmarkHeading $heading */
                    $heading = $benchmark;
                    $groupData['benchmarks'][] = array(
                        'heading' => true,
                        'name' => $this->getVariableSubstitution()->substitute($heading->getName()),
                        'description' => $this->getVariableSubstitution()->substitute($heading->getDescription())
                    );
                    continue;
                }

                /** @var \Mrss\Entity\BenchmarkHeading $benchmark */
                if ($this->isBenchmarkExcludeFromReport($benchmark)) {
                    continue;
                }

                $groupData['benchmarks'][] = $this->getBenchmarkData($benchmark);
            }

            $reportData[] = $groupData;

        }

        //prd($reportData);
        //echo '<pre>' . print_r($reportData, 1) . '</pre>';
        return $reportData;
    }

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

    public function download($reportData, $system = null)
    {
        $filename = 'national-report';
        if ($system) {
            $name = strtolower(str_replace(' ', '-', $system->getName()));
            $filename = $name . '-report';
        }

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            )
        );

        foreach ($reportData as $benchmarkGroup) {
            if (!empty($benchmarkGroup['timeframe'])) {
                $benchmarkGroup['benchmarkGroup'] .= ' (' . $benchmarkGroup['timeframe'] . ')';
            }

            // Header
            $headerRow = array(
                $benchmarkGroup['benchmarkGroup'],
                'Reported Value',
                '% Rank',
                'N'
            );

            foreach ($this->getPercentileBreakPointLabels() as $breakpoint) {
                $headerRow[] = strip_tags($breakpoint);
            }

            $sheet->fromArray($headerRow, null, 'A' . $row);
            $sheet->getStyle("A$row:I$row")->applyFromArray($blueBar);
            $row++;

            // Data
            foreach ($benchmarkGroup['benchmarks'] as $benchmark) {
                // Is this a subheading?
                if (!empty($benchmark['heading'])) {
                    $dataRow = array(
                        $benchmark['name']
                    );

                    $sheet->fromArray($dataRow, null, 'A' . $row);
                    $row++;
                    continue;
                }

                if (!empty($benchmark['timeframe'])) {
                    $benchmark['benchmark'] .= ' (' . $benchmark['timeframe'] . ')';
                }


                if (null !== $benchmark['reported']) {
                    $reported = $benchmark['prefix'] .
                        number_format(
                            $benchmark['reported'],
                            $benchmark['reported_decimal_places']
                        ) .
                        $benchmark['suffix'];
                } else {
                    $reported = null;
                };

                if ($benchmark['percentile_rank'] == '-') {
                    $rank = '-';
                } elseif ($benchmark['percentile_rank'] < 1) {
                    $rank = '<1%';
                    $benchmark['percentile_rank'] = $rank;
                } elseif ($benchmark['percentile_rank'] > 99) {
                    $rank = '>99%';
                } elseif ($benchmark['percentile_rank']) {
                    $rank = round($benchmark['percentile_rank']) . '%';
                } else {
                    $rank = null;
                }

                $dataRow = array(
                    $benchmark['benchmark'],
                    $reported,
                    $rank,
                    $benchmark['N']
                );

                foreach ($benchmark['percentiles'] as $percentile) {
                    $dataRow[] = $benchmark['prefix'] .
                        number_format(
                            $percentile,
                            $benchmark['reported_decimal_places']
                        ) . $benchmark['suffix'];
                }

                $sheet->fromArray($dataRow, null, 'A' . $row);
                $row++;
            }

            // Add a blank row after each form
            $row++;
        }

        // Align right
        $sheet->getStyle('B1:I400')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Set column widths
        PHPExcel_Shared_Font::setAutoSizeMethod(
            PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT
        );
        foreach (range(0, 8) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
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

        $manualExclude = in_array($benchmark->getDbColumn(), $toExclude);

        $inputTypesToExclude = array('radio');
        $inputTypeExclude = in_array(
            $benchmark->getInputType(),
            $inputTypesToExclude
        );

        // Now look at the checkbox
        if (!$benchmark->getIncludeInNationalReport()) {
            $manualExclude = true;
        }

        return ($manualExclude || $inputTypeExclude);
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

    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    public function getSystem()
    {
        return $this->system;
    }
}
