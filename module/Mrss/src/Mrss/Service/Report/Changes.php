<?php

namespace Mrss\Service\Report;

use Mrss\Entity\Subscription;
use Mrss\Entity\Benchmark;
use Mrss\Service\Report;
use Mrss\Entity\PercentChange;
use PHPExcel;

class Changes extends National
{
    //protected $percentThreshold = 5;

    protected $percentChangeModel;

    protected $changes;

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


    public function getData(Subscription $subscription, $system = null, $benchmarkGroupId = null)
    {
        $observation = $subscription->getObservation();
        $this->setObservation($observation);
        $college = $subscription->getCollege();
        $year = $subscription->getYear();
        $study = $this->getStudy();
        $dbColumns = $this->getIncludedDbColumns();

        $changes = $this->getPercentChangeModel()->findByCollegeAndYear($college, $year);

        $this->setChanges($changes);

        //$this->setObservation($observation);
        //$this->setSystem($system);
        //$year = $observation->getYear();
        $this->getVariableSubstitution()->setStudyYear($year);

        $reportData = array();

        $benchmarkGroups = $study->getBenchmarkGroupsBySubscription($subscription);
        foreach ($benchmarkGroups as $benchmarkGroup) {
            if (!empty($benchmarkGroupId) && $benchmarkGroup->getId() != $benchmarkGroupId) {
                continue;
            }

            $suppressed = $subscription->hasSuppressionFor($benchmarkGroup->getId());

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

                /** @var \Mrss\Entity\BenchmarkHeading $benchmark */
                if ($this->isBenchmarkExcludeFromReport($benchmark)) {
                    continue;
                }


                $benchmarkData = $this->getBenchmarkChangeData($benchmark);


                //$benchmarkData = $this->getBenchmarkData($benchmark);

                //$benchmarkData = array_merge($changeData, $benchmarkData);

                // Don't show them their own data if it's suppressed
                if ($suppressed) {
                    $benchmarkData['reported'] = null;
                }

                $groupData['benchmarks'][] = $benchmarkData;            }

            if (!empty($groupData['benchmarks'])) {
                $reportData[] = $groupData;
            }

        }

        return $reportData;
    }

    /**
     * @deprecated Use getData above instead
     * @param $changes
     * @param $year
     * @return array
     */
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
        $cols = $this->getStudyConfig()->percent_change_report_columns;
        $dbColumns = array();

        // Can't hand the config object directly back. Iterate to extract values
        foreach ($cols as $col) {
            $dbColumns[] = $col;
        }

        return $dbColumns;
    }

    public function isBenchmarkGroupEmpty($groupData)
    {
        $empty = true;
        foreach ($groupData['benchmarks'] as $benchmark) {
            if (empty($benchmark['heading'])) {
                $empty = false;
            }
        }

        return $empty;
    }

    public function prepareChanges($changes)
    {
        $newChanges = array();
        foreach ($changes as $change) {
            $newChanges[$change->getBenchmark()->getDbColumn()] = $change;
        }

        return $newChanges;
    }

    public function getBenchmarkChangeData($benchmark)
    {
        $changes = $this->getChanges();

        $percentChange = null;
        $data = array(
            'benchmark' => $benchmark->getReportLabel(),
        );

        if (!empty($changes[$benchmark->getDbColumn()])) {
            $change = $changes[$benchmark->getDbColumn()];

            $percentChange = $change->getPercentChange();

            $data = array(
                'benchmark' => $benchmark->getReportLabel(),
                'oldValue' => $benchmark->format($change->getOldValue()),
                'newValue' => $benchmark->format($change->getValue()),
                'percentChange' => round($percentChange) . '%'
            );
        } else {
            $data = array(
                'benchmark' => $benchmark->getReportLabel(),
                'oldValue' => null,
                'newValue' => null,
                'percentChange' => null
            );
        }

        $percentiles = $this->getBenchmarkData($benchmark, true, $percentChange);

        //if (!empty($data['oldValue'])) pr($data);
        $data = $data + $percentiles;

        //pr($percentiles);

        //pr($data);

        $data['reported_decimal_places'] = 0;
        $data['percentile_prefix'] = '';
        $data['percentile_suffix'] = '%';

        //if (!empty($data['oldValue'])) prd($data);

        return $data;
    }

    public function getDownloadHeader($formName)
    {
        $year = $this->getYear();

        // Header
        $headerRow = array(
            $formName,
            $year - 1,
            $year,
            '% Change',
            '% Rank',
            'N'
        );

        return $headerRow;
    }

    protected function getDownloadColWidth()
    {
        return 10;
    }

    protected function getDownloadLastCol()
    {
        return 'K';
    }

    protected function getDownloadFileName($system = null)
    {
        return 'percent-change-report';
    }

    protected function getDownloadDataRow($benchmark)
    {
        $percentileDecimalPlaces = 0;

        $rank = $benchmark['percentile_rank'];
        if (empty($benchmark['do_not_format_rank'])) {
            $rank = round($benchmark['percentile_rank']) . '%';
        }

        //pr($benchmark);

        $dataRow = array(
            $benchmark['benchmark'],
            $benchmark['oldValue'],
            $benchmark['newValue'],
            $benchmark['percentChange'],
            $rank,
            $benchmark['N']
        );

        foreach ($benchmark['percentiles'] as $percentile) {
            $dataRow[] = number_format(
                    $percentile,
                    $percentileDecimalPlaces
                ) . '%';
        }

        return $dataRow;
    }

    public function downloadDeleteMe($changes, $system = null)
    {
        prd($changes);



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

    public function setChanges($changes)
    {
        $this->changes = $this->prepareChanges($changes);

        return $this;
    }

    public function getChanges()
    {
        return $this->changes;
    }
}
