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
    protected $filename = 'nccbp-data';
    protected $studyIds = array();
    protected $studies = array();
    protected $studyModel;
    protected $subscriptionModel;

    public function getFullDataDump($studyIds)
    {
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
        // Quick fix for NCCBP
        $years = array();
        foreach (range(2007, 2013) as $year) {
            $years[$year] = array(1);
        }

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
        $this->excel->setActiveSheetIndexByName($year);

        $this->writeHeaders($year);
        $this->writeData($year);
    }

    protected function writeHeaders($year)
    {
        $sheet = $this->excel->getActiveSheet();

        $sheet->setCellValue('A1', 'IPEDS');
        $sheet->setCellValue('B1', 'Institution');

        $row = 1;
        $column = 2;

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
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
                $column++;
            }
        }
    }

    protected function writeData($year)
    {
        $sheet = $this->excel->getActiveSheet();

        $row = 3;
        $column = 2;

        // Get institutions that subscribed to any of the active studies for the year
        foreach ($this->getCollegesWithDataForYear($year) as $collegeInfo) {
            $college = $collegeInfo['college'];

            // Add the ipeds and name
            $sheet->setCellValueByColumnAndRow(0, $row, $college->getIpeds());
            $sheet->setCellValueByColumnAndRow(1, $row, $college->getName());

            $row++;
        }

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

                if (empty($colleges[$collegeId])) {
                    $colleges[$collegeId] = array(
                        'college' => $college,
                        'observation' => $subscription->getObservation,
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

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }
}
