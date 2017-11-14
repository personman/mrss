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

    /** @var \Mrss\Entity\Study $study */
    protected $study;

    protected $year = null;

    protected $systemId = null;

    protected $debug = false;

    protected $createColleges = false;


    //protected $file = 'data/imports/envisio-import-icma.xlsx';
    //protected $file = 'data/imports/envisio-import-safety.xlsx';
    //protected $file = 'data/imports/full-icma-import.xlsx';
    //protected $file = 'data/imports/vbc-import-final.xlsx';

    protected $file = 'data/imports/mobility.xlsx';

    protected $auditService;

    protected $map = array();

    protected $multPercent = false;

    public function import($serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->excel = $this->openFile($this->file);

        $sheets = $this->getSheets();

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

        return array(
            'status' => 'success'
        );
    }

    protected function getSheets()
    {
        $allSheets = $this->excel->getWorksheetIterator();

        $sheets = array();
        foreach ($allSheets as $sheet) {
            $sheets[$this->excel->getIndex($sheet)] = $sheet->getTitle();
        }

        return $sheets;
    }

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        $data = $this->getDataFromRow($row);

        //prd($data);

        if (empty($data['ipeds'])) {
            $name = $data['name'];
            $nameParts = explode(', ', $name);

            if (count($nameParts) > 1) {
                $name = $nameParts[0];
                $state = $nameParts[1];

                $college = $this->getCollegeModel()->findByNameAndState($name, $state);
            }


            if (!empty($college)) {
                $data['ipeds'] = $college->getIpeds();

                if ($this->debug) {
                    echo "<p>Found city by name ($name) and state ($state): {$college->getNameAndState()}</p>";
                }
            } else {
                if ($this->debug) {
                    echo "<p>Unable to find by city ($name) and state ($state).</p>";
                }

                if ($this->createColleges) {
                    // Create college
                    $ipeds = $this->getCollegeModel()->findMaxIpeds();
                    $ipeds++;

                    $collegeInfo = array(
                        'ipeds' => $ipeds,
                        'name' => $name,
                        'state' => $state,
                        'abbreviation' => '',

                    );


                    $this->createCollege($collegeInfo);

                    $data['ipeds'] = $ipeds;

                    if ($this->debug) {
                        echo "<p>Created city ($name).</p>";
                    }
                }
            }
        }

        $ipeds = $data['ipeds'];
        unset($data['ipeds']);

        $college = $this->getCollege($ipeds);

        if (!$college) {
            // Create it
        }



        if ($college) {
            $subscription = $this->getSubscriptionModel()
                ->findOne($this->year, $college->getId(), $this->study->getId());

            if (empty($subscription)) {
                $subscription = $this->createSubscription($college);
            }

            $year = $subscription->getYear();
            $subscription->setBenchmarkModel($this->getBenchmarkmodel());
            $subscription->setDatumModel($this->getDatumModel());
            $oldData = $subscription->getAllData();

            foreach ($data as $dbColumn => $value) {
                $value = $this->processValue($dbColumn, $value);
                if (empty($value)) {
                    $value = null;
                }
                $subscription->setValue($dbColumn, $value);

                if ($this->debug) {
                    echo "<p>$year: {$college->getName()} - $dbColumn: $value</p>";
                }
            }

            $newData = $subscription->getAllData();

            $this->getAuditService()->logChangesNew($oldData, $newData, 'admin-import', $subscription);

            $this->getSubscriptionModel()->save($subscription);
            $this->getSubscriptionModel()->getEntityManager()->flush();

            // Add system membership
            $this->connectToSystem($college);
        } else {
            if ($this->debug) {
                echo "<p>Ipeds present ({$data['ipeds']}) but unable to find city.</p>";
            }
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
        $benchmark = $this->getBenchmarkmodel()->findOneByDbColumn($dbColumn);

        $value = trim($value);
        if (stristr($value, ':')) {
            $valueParts = explode(':', $value);
            $minutes = $valueParts[0];
            $seconds = $valueParts[1];

            $minuteSeconds = $minutes * 60;
            $seconds += $minuteSeconds;
            $value = $seconds;
        }

        // Strip out % and $
        $value = str_replace(array('%', '$'), array('', ''), $value);


        if (stristr($value, '.')) {
            if ($benchmark) {
                if ($benchmark->isPercent() && $benchmark->getDbColumn() != 'vcr1' && $this->multPercent) {
                    $value = $value * 100;
                }
            } else {
                echo 'Benchmark not found for ' . $dbColumn;
            }
        }

        if ($benchmark && $benchmark->isNumericalRadio()) {
            $options = $benchmark->getOptionsForForm();

            $flipped = array_flip($options);

            if (isset($flipped[$value])) {
                $value = $flipped[$value];
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

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return \Mrss\Service\ObservationAudit $observationAudit
     */
    protected function getAuditService()
    {
        if (empty($this->auditService)) {
            $this->auditService = $this->serviceManager->get('service.observationAudit');
        }

        return $this->auditService;
    }
}
