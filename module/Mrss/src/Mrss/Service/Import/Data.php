<?php

namespace Mrss\Service\Import;

use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\Subscription as SubscriptionEntity;
use Mrss\Entity\College as CollegeEntity;

class Data extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\Subscription $subscriptionModel */
    protected $subscriptionModel;

    /** @var \Mrss\Model\Observation $observationModel */
    protected $observationModel;

    /** @var \Mrss\Model\Benchmark $benchmarkmodel */
    protected $benchmarkModel;

    /** @var \Mrss\Model\Datum $datumModel */
    protected $datumModel;

    /** @var \Mrss\Entity\Study $stuyd */
    protected $study;

    protected $year = 2014;


    protected $file = 'data/imports/envisio-import.xlsx';

    protected $map = array();

    public function import()
    {
        $this->excel = $this->openFile($this->file);

        $sheets = array(
            0 => 2014,
            1 => 2015,
            2 => 2016
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
                $subscription->setValue($dbColumn, $value);
            }

            $this->getSubscriptionModel()->save($subscription);
            $this->getSubscriptionModel()->getEntityManager()->flush();

            pr($college->getName());
            pr($data);

        }
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
        $map = array(
            'name', // Not used
            'ipeds',
            'population',
            'median_household_income',
            'poverty',
            'fireresponse',
            'totalfireservicecalls',
            'policeresponsetimes',
            'police1',
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



}