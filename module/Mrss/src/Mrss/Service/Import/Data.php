<?php

namespace Mrss\Service\Import;

use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\Subscription as SubscriptionEntity;
use Mrss\Entity\College as CollegeEntity;
use Mrss\Entity\SystemMembership;
use PHPExcel_Cell;

class Data extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\Subscription $subscriptionModel */
    protected $subscriptionModel;

    /** @var \Mrss\Model\System $systemModel */
    protected $systemModel;

    /** @var \Mrss\Model\SystemMembership $systemMembershipModel */
    protected $membershipModel;

    /** @var \Mrss\Model\Observation $observationModel */
    protected $observationModel;

    /** @var \Mrss\Model\Benchmark $benchmarkmodel */
    protected $benchmarkModel;

    /** @var \Mrss\Model\Datum $datumModel */
    protected $datumModel;

    /** @var \Mrss\Entity\Study $stuyd */
    protected $study;

    protected $year = 2015;

    protected $systemId = null;


    //protected $file = 'data/imports/envisio-import-icma.xlsx';
    protected $file = 'data/imports/envisio-import-safety.xlsx';

    protected $map = array();

    public function import()
    {
        $this->excel = $this->openFile($this->file);

        $sheets = array(
            //0 => 2014,
            1 => 2015,
            //2 => 2016
        );

        foreach ($sheets as $index => $year) {
            $this->year = $year;

            $this->excel->setActiveSheetIndex($index);
            $sheet = $this->excel->getActiveSheet();
            $count = 0;

            foreach ($sheet->getRowIterator() as $row) {
                /** @var PHPExcel_Worksheet_Row $row */
                $rowIndex = $row->getRowIndex();

                // Skip the header row
                if ($rowIndex === 1 || $rowIndex == 2) {
                    continue;
                }

                $this->saveRow($row);
                $count++;
            }
        }


        die('import finished');
    }

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        $data = $this->getDataFromRow($row);

        if (empty($data['ipeds'])) {
            return false;
        }

        $ipeds = $data['ipeds'];
        unset($data['ipeds']);

        if ($college = $this->getCollege($ipeds)) {
            $subscription = $this->getSubscriptionModel()->findOne($this->year, $college->getId(), $this->study->getId());

            if (empty($subscription)) {
                $subscription = $this->createSubscription($college);
            }

            $subscription->setBenchmarkModel($this->getBenchmarkmodel());
            $subscription->setDatumModel($this->getDatumModel());

            foreach ($data as $dbColumn => $value) {
                $value = $this->processValue($dbColumn, $value);
                if (empty($value)) {
                    $value = null;
                }
                $subscription->setValue($dbColumn, $value);
            }

            $this->getSubscriptionModel()->save($subscription);
            $this->getSubscriptionModel()->getEntityManager()->flush();

            // Add system membership
            $this->connectToSystem($college);
        }
    }

    protected function connectToSystem($college)
    {
        if ($this->systemId) {
            $system = $this->getSystemModel()->find($this->systemId);

            // See if the membership exists
            $membership = $this->getSystemMembershipModel()->findBySystemCollegeYear($system, $college, $this->year);

            if (!$membership) {
                $membership = new SystemMembership();
                $membership->setSystem($system);
                $membership->setCollege($college);
                $membership->setYear($this->year);
                $membership->setDataVisibility('public');

                $this->getSystemMembershipModel()->save($membership);
            }
        }
    }

    protected function processValue($dbColumn, $value)
    {
        $value = trim($value);
        if (stristr($value, ':')) {
            $valueParts = explode(':', $value);
            $minutes = $valueParts[0];
            $seconds = $valueParts[1];

            $minuteSeconds = $minutes * 60;
            $seconds += $minuteSeconds;
            $value = $seconds;
        }

        if (stristr($value, '.')) {
            $benchmark = $this->getBenchmarkmodel()->findOneByDbColumn($dbColumn);
            if ($benchmark) {
                if ($benchmark->isPercent()) {
                    $value = $value * 100;
                }

            } else {
                echo 'Benchmark not found for ' . $dbColumn;
            }
        }


        return $value;
    }

    protected function getCollege($ipeds)
    {
        return $this->getCollegeModel()->findOneByIpeds($ipeds);
    }

    protected function createSubscription($college)
    {
        $subscription = new SubscriptionEntity();
        $subscription->setCompletion(0);
        $status = 'complete';
        $method = 'free';
        $amount = 0;

        $subscription->setYear($this->year);
        $subscription->setStatus($status);
        $subscription->setCollege($college);
        $subscription->setStudy($this->study);
        $subscription->setPaymentMethod($method);
        $subscription->setPaymentAmount($amount);

        $observation = $this->getOrCreateObservation($college);
        $subscription->setObservation($observation);


        $this->getSubscriptionModel()->save($subscription);
        $this->getSubscriptionModel()->getEntityManager()->flush();

        return $subscription;
    }

    protected function getOrCreateObservation($college)
    {
        $observationModel = $this->getObservationModel();

        $observation = $observationModel->findOne(
            $college->getId(),
            $this->year
        );

        if (empty($observation)) {
            $observation = new \Mrss\Entity\Observation;
            $observation->setMigrated(true);
        }

        $observation->setYear($this->year);
        $observation->setCollege($college);

        $observationModel->save($observation);

        $observationModel->getEntityManager()->flush();

        return $observation;
    }


    /**
     * @return \Mrss\Model\College
     */
    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    /**
     * @param \Mrss\Model\College $collegeModel
     * @return Data
     */
    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;
        return $this;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    /**
     * @param \Mrss\Model\Subscription $subscriptionModel
     * @return Data
     */
    public function setSubscriptionModel($subscriptionModel)
    {
        $this->subscriptionModel = $subscriptionModel;
        return $this;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkmodel()
    {
        return $this->benchmarkModel;
    }

    /**
     * @param \Mrss\Model\Benchmark $benchmarkmodel
     * @return Data
     */
    public function setBenchmarkmodel($benchmarkModel)
    {
        $this->benchmarkModel = $benchmarkModel;
        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param \Mrss\Entity\Study $study
     * @return Data
     */
    public function setStudy($study)
    {
        $this->study = $study;
        return $this;
    }


    /**
     * Maps excel columns to College entity property names
     * @return array
     */
    protected function getMap()
    {
        if (count($this->map) == 0) {
            //$headerRow = $this->excel->getActiveSheet()->row
            //$rowData[$property] = $this->excel->getActiveSheet()->getCellByColumnAndRow($column, $rowIndex)->getValue();


            $row = $this->excel->getActiveSheet()->getRowIterator(2)->current();

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $this->map['name'] = 0; // Placeholder

            foreach ($cellIterator as $key => $cell) {
                if ($cell->getValue()) {
                    //$coordinate = $cell->getCoordinate();
                    //$column = $cell->getColumn();
                    $column = PHPExcel_Cell::columnIndexFromString($cell->getColumn());

                    $this->map[$cell->getValue()] = $column - 1;
                }

            }

        }

        //pr($this->map);

        return $this->map;

        $map = array(
            'name', // Not used
            'ipeds',
            'population',
            'median_household_income',
            'poverty',
            'fireresponse',
            'totalfireservicecalls',
            'policeresponsetimes',
            'policecalls1',
            'vcr1',
            'pcr',
            'vccr',
            'pccr',
            'police2',
            'libraries',
            'libraries_per',
            'libraries1',
            'park',
            'trails',
            'waterbill',
            'sewerbills',
            'waterlow',
            'sewerbills2',
            'trashbill',
            'wastediv',
            'employ1',
            'bondrating'
        );

        $withNumbers = array();
        $iteration = 0;

        foreach ($map as $key) {
            $withNumbers[$key] = $iteration++;
        }

        return $withNumbers;
    }

    /**
     * @return \Mrss\Model\Observation
     */
    public function getObservationModel()
    {
        return $this->observationModel;
    }

    /**
     * @param \Mrss\Model\Observation $observationModel
     * @return Data
     */
    public function setObservationModel($observationModel)
    {
        $this->observationModel = $observationModel;
        return $this;
    }

    /**
     * @return \Mrss\Model\Datum
     */
    public function getDatumModel()
    {
        return $this->datumModel;
    }

    /**
     * @param \Mrss\Model\Datum $datumModel
     * @return Data
     */
    public function setDatumModel($datumModel)
    {
        $this->datumModel = $datumModel;
        return $this;
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->systemModel;
    }

    /**
     * @param \Mrss\Model\System $model
     * @return Data
     */
    public function setSystemModel($model)
    {
        $this->systemModel = $model;
        return $this;
    }

    public function setSystemMembershipModel($model)
    {
        $this->membershipModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\SystemMembership
     */
    public function getSystemMembershipModel()
    {
        return $this->membershipModel;
    }
}
