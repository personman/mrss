<?php

namespace Mrss\Service\Report;

use Mrss\Entity\Benchmark;
use Mrss\Service\Report;
use Mrss\Entity\PercentChange;
use PHPExcel;

class Changes extends Report
{
    //protected $percentThreshold = 5;

    protected $percentChangeModel;

    public function calculateChanges($observationId)
    {
        if ($observation = $this->getObservationModel()->find($observationId)) {
            $year = $observation->getYear();
            $this->clearChangesForCollege($observation->getCollege(), $year);

            // Now get the previous year's observation
            $previousYear = $year - 1;

            $collegeId = $observation->getCollege()->getId();

            if ($previousObservation = $this->getObservationModel()->findOne($collegeId, $previousYear)) {
                // Now loop over all benchmarks
                $benchmarks = $this->getStudy()->getAllBenchmarks();
                foreach ($benchmarks as $benchmark) {
                    $dbColumn = $benchmark->getDbColumn();
                    $value = $observation->get($dbColumn);
                    $previousValue = $previousObservation->get($dbColumn);

                    if (!is_null($value) && !is_null($previousValue) && $previousValue != 0) {
                        $percentDifference = $this->compareValues($value, $previousValue);

                        //if (abs($percentDifference) >= $this->percentThreshold)
                        $this->recordChange($value, $previousValue, $benchmark, $observation, $percentDifference);
                    }
                }
            }
        }

        // Persist
        $this->getPercentChangeModel()->getEntityManager()->flush();
    }

    public function compareValues($value, $previousValue)
    {
        $difference = $previousValue - $value;

        $percentDifference = $difference / $previousValue * 100 * -1;

        return $percentDifference;
    }

    public function recordChange($value, $previousValue, $benchmark, $observation, $percentDifference)
    {
        //pr($value);pr($previousValue);pr($benchmark->getDescriptiveReportLabel());pr($percentDifference);
        $change = new PercentChange();
        $change->setValue($value);
        $change->setOldValue($previousValue);
        $change->setPercentChange($percentDifference);
        $change->setBenchmark($benchmark);
        $change->setYear($observation->getYear());
        $change->setCollege($observation->getCollege());
        $change->setStudy($this->getStudy());

        $this->getPercentChangeModel()->save($change);
    }

    public function clearChangesForCollege($college, $year)
    {
        $this->getPercentChangeModel()->deleteByCollegeAndYear($college, $year);
    }

    /**
     * @return \Mrss\Model\PercentChange
     */
    public function getPercentChangeModel()
    {
        return $this->percentChangeModel;
    }

    /**
     * @param mixed $percentChangeModel
     * @return Changes
     */
    public function setPercentChangeModel($percentChangeModel)
    {
        $this->percentChangeModel = $percentChangeModel;
        return $this;
    }

    public function getReport($changes, $year)
    {
        $changes = $this->prepareChanges($changes);

        $report = array();

        $study = $this->getStudy();
        $dbColumns = $this->getIncludedDbColumns();

        $benchmarkGroups = $study->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            $groupData = array(
                'benchmarkGroup' => $benchmarkGroup->getName(),
                'timeframe' => $this->getVariableSubstitution()->substitute($benchmarkGroup->getTimeframe()),
                'url' => $benchmarkGroup->getUrl(),
                'benchmarks' => array()
            );
            $benchmarks = $benchmarkGroup->getChildren($year, true, 'report', $dbColumns);

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

                // Skip benchmark if it's not in the included list
                /*if (!in_array($benchmark->getDbColumn(), $dbColumns)) {
                    continue;
                }*/

                /** @var \Mrss\Entity\BenchmarkHeading $benchmark */
                /*if ($this->isBenchmarkExcludeFromReport($benchmark)) {
                    continue;
                }*/

                $benchmarkData = $this->getBenchmarkChangeData($benchmark, $changes);
                //$benchmarkData = array('test');
                
                // Don't show them their own data if it's suppressed
                /*if ($suppressed) {
                    $benchmarkData['reported'] = null;
                }*/

                $groupData['benchmarks'][] = $benchmarkData;
            }

            if (!empty($groupData['benchmarks'])) {
                $report[] = $groupData;
            }

        }

        return $report;
    }

    public function getIncludedDbColumns()
    {
        return array(
            'ft_cr_head',
            'tuition_fees',
            'ft_minus4_perc_completed',
            'pt_minus4_perc_completed',
            'ft_perc_transf',
            'pt_perc_transf',
            'ft_minus4_perc_comp_and_transf',
            'pt_minus4_perc_comp_and_transf',
            'ft_minus7_perc_completed',
            'pt_minus7_perc_completed',
            'percminus7_transf',
            'pt_percminus7_tran',
            'ft_minus7_perc_comp_and_transf',
            'pt_minus7_perc_comp_and_transf',
            'cst_crh',
            'cst_fte_stud'
        );
    }

    public function isBenchmarkGroupEmpty($groupData)
    {
        $empty = true;
        foreach ($groupData['benchmarks'] as $benchmark) {
            if (empty($benchmark['heading'])) {
                $empty = false;
            }
        }
    }

    public function prepareChanges($changes)
    {
        $newChanges = array();
        foreach ($changes as $change) {
            $newChanges[$change->getBenchmark()->getDbColumn()] = $change;
        }

        return $newChanges;
    }

    public function getBenchmarkChangeData($benchmark, $changes)
    {
        $data = array(
            'benchmark' => $benchmark->getReportLabel(),
        );

        if (!empty($changes[$benchmark->getDbColumn()])) {
            $change = $changes[$benchmark->getDbColumn()];

            $data = array(
                'benchmark' => $benchmark->getReportLabel(),
                'oldValue' => $benchmark->format($change->getOldValue()),
                'newValue' => $benchmark->format($change->getValue()),
                'percentChange' => round($change->getPercentChange()) . '%'
            );
        }

        return $data;
    }

    public function download($changes)
    {
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Headers
        $headers = array(
            'Institution',
            'Form',
            'Benchmark',
            'Old Value',
            'New Value',
            'Percent Difference'
        );

        $sheet->fromArray($headers, null, 'A1');
        $row++;

        foreach ($changes as $change) {
            $rowData = array(
                $change->getCollege()->getNameAndState(),
                $change->getBenchmark()->getBenchmarkGroup()->getUrl(),
                $change->getBenchmark()->getDescriptiveReportLabel(),
                $change->getOldValue(),
                $change->getValue(),
                round($change->getPercentChange())
            );

            $sheet->fromArray($rowData, null, 'A' . $row, true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);


        $filename = 'percent-changes';
        $this->downloadExcel($excel, $filename);
    }
}
