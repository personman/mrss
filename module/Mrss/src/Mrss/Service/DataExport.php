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
        $start = microtime(1);

        $this->year = $year;
        $this->studyIds = $studyIds;

        $this->startCsvFile();

        // Start building the Excel file
        //$this->excel = new PHPExcel();

        // Add a sheet for each year
        foreach ($this->getYears() as $year => $studies) {
            $this->addSheetForYearAndStudies($year, $studies);
        }

        // Remove the default sheet
        /*$sheetIndex = $this->excel->getIndex(
            $this->excel->getSheetByName('Worksheet')
        );
        $this->excel->removeSheetByIndex($sheetIndex);*/







        // Debug

        $elapsed = microtime(1) - $start;
        $elapsed = round($elapsed, 3);


        $logger = $this->getSubscriptionModel()->getEntityManager()->getConfiguration()->getSQLLogger();

        $this->queryLogger($logger);


        $mem = memory_get_peak_usage();
        $mem = round($mem / (1024 * 1024), 2);

        echo 'Memory used in MB:';
        pr($mem);

        echo 'rows processed:';
        pr($this->debugLimit);



        echo 'Elapsed seconds:';
        pr($elapsed);

        die('debug');

        // Send the file for download
        $this->download();
    }

    protected function queryLogger($logger)
    {
        $tables = array();
        $params = array();
        $queryTime = 0;

        foreach ($logger->queries as $query) {
            if (array_key_exists('executionMS', $query)) {
                $queryTime += $query['executionMS'];
            }


            $sql = $query['sql'];
            //pr($sql);

            $table = $this->getTableFromSql($sql);

            if (empty($tables[$table])) {
                $tables[$table] = 1;
            } else {
                $tables[$table]++;
            }

            $qParams = $query['params'];
            if ($table && isset($qParams[0])) {
                $param = $qParams[0];

                if ($param == 'ft_male_no_rank_number_12_month') {
                    //pr($sql);
                }

                if (!isset($params[$param])) {
                    $params[$param] = 1;
                } else {
                    $params[$param]++;
                }
            }
        }

        pr($tables);

        asort($params);

        echo 'Query time:';
        pr($queryTime);

        //pr($params);

        //die('tewt');
    }

    protected function getTableFromSql($sql)
    {
        preg_match('/(FROM|UPDATE) (.*?) /', $sql, $matches);

        $table = null;
        if (!empty($matches[2])) {
            $table = $matches[2];
        }

        return $table;
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
        //$this->writeData($year);

        $allData = $this->getSubscriptionModel()->findAllWithData(4, $year);
        //pr($allData);


        //$sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
    }

    protected function writeHeaders($year)
    {
        $headers = array(
            'IPEDS',
            'Institution',
            'State'
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

    protected function writeData($year)
    {

        //$sheet = $this->excel->getActiveSheet();

        $row = 1;
        $dataStartingColumn = 2;


        // Get institutions that subscribed to any of the active studies for the year
        $data = array();
        foreach ($this->getCollegesWithDataForYear($year) as $collegeInfo) {

            // Debug:
            if ($row > $this->debugLimit) {
                continue;
            }


            $dataRow = array();

            $college = $collegeInfo['college'];

            // Add the ipeds and name
            $dataRow[] = $college->getIpeds();
            $dataRow[] = $college->getName();
            $dataRow[] = $college->getState();

            //$sheet->setCellValueByColumnAndRow(0, $row, $college->getIpeds());
            //$sheet->setCellValueByColumnAndRow(1, $row, $college->getName());

            // Add the data
            //$observation = $collegeInfo['observation'];
            /** @var \Mrss\Entity\Subscription $subscription */
            $subscription = $collegeInfo['subscription'];
            $subData = $subscription->getAllData();

            $column = $dataStartingColumn;
            foreach ($collegeInfo['studies'] as $study) {

                $benchmarks = $this->getBenchmarks($study->getId());

                foreach ($benchmarks as $benchmark) {
                    //$value = $subscription->getValue($benchmark->getDbColumn());
                    $value = $subData[$benchmark->getDbColumn()];
                    $dataRow[] = $value;

                    $column++;
                }

                //$this->getSubscriptionModel()->getEntityManager()->flush();
            }

            //$data[] = $dataRow;

            $this->addCsvRow($dataRow);

            $row++;

            $entityManager = $this->getSubscriptionModel()->getEntityManager();
            $entityManager->detach($college);
            $entityManager->detach($subscription);

            unset($college);
            unset($subscription);
            unset($dataRow);
        }

        //$sheet->fromArray($data, null, 'A4', true);


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

                //$observation = $subscription->getObservation();

                /*if (empty($subscription)) {
                    continue;
                }*/

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

    protected function downloadOld()
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
