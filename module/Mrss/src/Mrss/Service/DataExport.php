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
    protected $filename = '/tmp/full-data-export.csv';
    protected $studyIds = array();
    protected $studies = array();
    protected $studyModel;
    protected $subscriptionModel;
    protected $debugLimit = 20;
    protected $benchmarks = array();
    protected $year;

    public function getFullDataDump($studyIds, $year)
    {
        // This may take some time (and RAM)
        takeYourTime();

        $this->year = $year;
        $this->studyIds = $studyIds;

        $this->startCsvFile();

        // Add a sheet for each year
        foreach ($this->getYears() as $studyYear => $studies) {
            if ($year != $studyYear) {
                continue;
            }

            $this->addSheetForYearAndStudies($year, $studies);
        }

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

    /**
     * @param $studyId
     * @return \Mrss\Entity\Study
     * @throws \Exception
     */
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

    protected function startCsvFile()
    {
        $file = fopen($this->filename, 'w');
        
        fclose($file);
    }
    
    protected function addCsvRow($row)
    {
        $file = fopen($this->filename, 'a');

        fputcsv($file, $row);
        
        fclose($file);
    }
    
    protected function addSheetForYearAndStudies($year, $studies)
    {
        // Create a new worksheet
        //$sheet = new PHPExcel_Worksheet($this->excel, "$year");
        //$this->excel->addSheet($sheet);
        //$this->excel->setActiveSheetIndexByName("$year");

        $this->writeHeaders($year);
        $this->writeData($year, $studies);


        //pr($allData);


        //$sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
    }

    protected function writeHeaders($year)
    {
        $headers = array(
            'Name',
            'ipeds',
        );

        $headers2 = $headers3 = array(null, null, null);


        $row = 1;
        $column = 3;
        $names = array();

        // Add the benchmarks from each study
        foreach ($this->getStudies() as $study) {
            $benchmarks = $study->getBenchmarksForYear($year);

            foreach ($benchmarks as $benchmark) {
                $headers[] = $benchmark->getName();

                $headers2[] = $benchmark->getDbColumn();

                $headers3[] = $benchmark->getBenchmarkGroup()->getName();

                /*
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
                $column++;*/
            }
        }

        $this->addCsvRow($headers);
        $this->addCsvRow($headers2);
        $this->addCsvRow($headers3);
    }

    /**
     * @param $studyId
     * @return \Mrss\Entity\Benchmark[]
     * @throws \Exception
     */
    protected function getBenchmarks($studyId)
    {
        if (empty($this->benchmarks[$studyId])) {
            $this->benchmarks[$studyId] = $this->getStudy($studyId)->getBenchmarksForYear($this->year);
        }

        return $this->benchmarks[$studyId];
    }

    protected function writeData($year, $studies)
    {
        $studyId = array_pop($studies);
        $benchmarks = $this->getBenchmarks($studyId);
        $dbColumns = array();
        foreach ($benchmarks as $benchmark) {
            $dbColumns[] = $benchmark->getDbColumn();
        }

        $allData = $this->getSubscriptionModel()->findAllWithData($studyId, $year);

        foreach ($allData as $row) {
            $data = $row['data'];
            unset($row['data']);

            $newData = array();
            foreach ($dbColumns as $dbColumn) {
                $value = null;
                if (array_key_exists($dbColumn, $data)) {
                    $value = $data[$dbColumn];
                }

                $newData[] = $value;
            }

            $row = array_merge($row, $newData);

            $this->addCsvRow($row);
        }
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
                        'subscription' => $subscription,
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
        if (file_exists($this->filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($this->filename).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->filename));

            readfile($this->filename);

            exit;
        } else {
            die('error: file does not exist');
        }
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
