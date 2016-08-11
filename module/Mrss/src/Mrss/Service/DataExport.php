<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;
use Mrss\Entity\Subscription;

use Mrss\Entity\Benchmark;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;

/**
 * Class DataExport
 *
 * Full data dump for policy analysts. All years, all colleges, all benchmarks.
 * Each year is a separate sheet. Each row is a college. Each column is a benchmark.
 *
 * @package Mrss\Service
 */
class DataExport
{
    /**
     * @var PHPExcel $excel
     */
    protected $excel;
    protected $filename = 'full-data-export';
    protected $studyIds = array();
    protected $studies = array();
    protected $studyModel;
    protected $subscriptionModel;

    public function getFullDataDump($studyIds)
    {
        // This may take some time (and RAM)
        takeYourTime();

        $this->studyIds = $studyIds;

        // Start building the Excel file
        $this->excel = new PHPExcel();

        // Add a sheet for each year
        foreach ($this->getYears() as $year => $studies) {
            $this->addSheetForYearAndStudies($year, $studies);
        }

        // Remove the default sheet
        $sheetIndex = $this->excel->getIndex(
            $this->excel->getSheetByName('Worksheet')
        );
        $this->excel->removeSheetByIndex($sheetIndex);

        // Send the file for download
        $this->download();
    }

    /**
     * Return an array of studies for years that have data and the studies.
     */
    protected function getYears()
    {
        $years = array();
        foreach ($this->getStudies() as $study) {
            $studyYears = $this->getSubscriptionModel()->getYearsWithSubscriptions($study);

            foreach ($studyYears as $year) {
                if (empty($years[$year])) {
                    $years[$year] = array();
                }

                $studyId = $study->getId();
                $years[$year][] = $studyId;
            }
        }

        // Limit years for performance reasons.
        /*if ($studyId == 1) {
            $years = array(2015 => array($studyId), 2014 => array($studyId));
        }*/

        $years = array(2016 => array($studyId));
        return $years;
    }

    protected function getStudies()
    {
        if (empty($this->studies)) {
            foreach ($this->studyIds as $studyId) {
                if ($study = $this->getStudyModel()->find($studyId)) {
                    $this->studies[$studyId] = $study;
                }
            }
        }

        return $this->studies;
    }

    protected function getStudy($studyId)
    {
        $studies = $this->getStudies();

        if (!empty($studies[$studyId])) {
            $study = $studies[$studyId];
        } else {
            throw new \Exception("Study $studyId not found.");
        }

        return $study;
    }

    protected function addSheetForYearAndStudies($year, $studies)
    {
        // Create a new worksheet
        $sheet = new PHPExcel_Worksheet($this->excel, "$year");
        $this->excel->addSheet($sheet);
        $this->excel->setActiveSheetIndexByName("$year");

        $this->writeHeaders($year);
        $this->writeData($year);

        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
    }

    protected function writeHeaders($year)
    {
        $sheet = $this->excel->getActiveSheet();

        $sheet->setCellValue('A1', 'IPEDS');
        $sheet->setCellValue('B1', 'Institution');
        $sheet->setCellValue('C1', 'State');

        $row = 1;
        $column = 3;
        $names = array();

        // Add the benchmarks from each study
        foreach ($this->getStudies() as $study) {
            $benchmarks = $study->getBenchmarksForYear($year);

            foreach ($benchmarks as $benchmark) {
                $sheet->setCellValueByColumnAndRow(
                    $column,
                    $row,
                    $benchmark->getName()
                );
                $sheet->setCellValueByColumnAndRow(
                    $column,
                    $row + 1,
                    $benchmark->getDbColumn()
                );

                // Form name
                $sheet->setCellValueByColumnAndRow(
                    $column,
                    $row + 2,
                    $benchmark->getBenchmarkGroup()->getName()
                );
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
                $column++;
            }
        }

        if (false && $year == '2013') {
            $dupes = array();
            foreach ($names as $name => $dbColumns) {
                if (count($dbColumns) == 1) {
                    continue;
                }

                foreach ($dbColumns as $d) {
                    $dupes[$d] = $name;
                }
            }

            echo '<pre>';
            //print_r($dupes);
            //die;
        }
    }

    protected function writeData($year)
    {
        $sheet = $this->excel->getActiveSheet();

        $row = 4;
        $dataStartingColumn = 2;

        // Get institutions that subscribed to any of the active studies for the year
        $data = array();
        foreach ($this->getCollegesWithDataForYear($year) as $collegeInfo) {
            $dataRow = array();

            $college = $collegeInfo['college'];

            // Add the ipeds and name
            $dataRow[] = $college->getIpeds();
            $dataRow[] = $college->getName();
            $dataRow[] = $college->getState();

            //$sheet->setCellValueByColumnAndRow(0, $row, $college->getIpeds());
            //$sheet->setCellValueByColumnAndRow(1, $row, $college->getName());

            // Add the data
            $observation = $collegeInfo['observation'];

            $column = $dataStartingColumn;
            foreach ($collegeInfo['studies'] as $study) {
                $benchmarks = $study->getBenchmarksForYear($year);

                foreach ($benchmarks as $benchmark) {
                    if ($observation->has($benchmark->getDbColumn())) {
                        $value = $observation->get($benchmark->getDbColumn());
                    } else {
                        $value = '';
                    }

                    $dataRow[] = $value;

                    $column++;
                }
            }

            $data[] = $dataRow;

            $row++;
        }

        $sheet->fromArray($data, null, 'A4', true);

        // Add the benchmarks from each study
        /*foreach ($this->getStudies() as $study) {
            $benchmarks = $study->getBenchmarksForYear($year);

            foreach ($benchmarks as $benchmark) {
                $sheet->setCellValueByColumnAndRow(
                    $column,
                    $row,
                    $benchmark->getName()
                );

                $column++;
            }
        }*/
    }

    protected function getCollegesWithDataForYear($year)
    {
        $colleges = array();

        foreach ($this->getStudies() as $study) {
            $subscriptions = $this->getSubscriptionModel()
                ->findByStudyAndYear($study->getId(), $year);

            foreach ($subscriptions as $subscription) {
                $college = $subscription->getCollege();
                $collegeId = $college->getId();

                $study = $subscription->getStudy();
                $studyId = $study->getId();

                $observation = $subscription->getObservation();

                if (empty($observation)) {
                    continue;
                }

                if (empty($colleges[$collegeId])) {
                    $colleges[$collegeId] = array(
                        'college' => $college,
                        'observation' => $observation,
                        'studies' => array()
                    );
                }

                $colleges[$collegeId]['studies'][$studyId] = $study;
            }
        }
        return $colleges;
    }


    protected function download()
    {
        // redirect output to client browser
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    public function setStudyModel($model)
    {
        $this->studyModel = $model;

        return $this;
    }

    public function getStudyModel()
    {
        return $this->studyModel;
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }
}
