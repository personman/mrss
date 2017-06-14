<?php

namespace Mrss\Service\Import;

use Mrss\Entity\System;
use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\College as CollegeEntity;

class College extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\System $system */
    protected $systemModel;

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        $data = $this->getDataFromRow($row);

        if (empty($data['name'])) {
            return false;
        }

        $entity = $this->getEntity($data);

        if (!empty($data['systemName']) && $system = $this->saveSystem($data['systemName'])) {
            $entity->setSystem($system);
        }

        $this->getCollegeModel()->save($entity);
    }

    protected function getDataFromRow(PHPExcel_Worksheet_Row $row)
    {
        $rowIndex = $row->getRowIndex();

        $rowData = array();
        foreach ($this->getMap() as $property => $column) {
            $rowData[$property] = trim($this->excel->getActiveSheet()->getCellByColumnAndRow($column, $rowIndex)->getValue());
        }

        return $rowData;
    }

    /**
     * Maps excel columns to College entity property names
     * @return array
     */
    protected function getMap()
    {
        return array(
            //'opeId' => 0,
            'ipeds' => 2,
            //'systemName' => 2,
            'name' => 0,
            //'address' => 4,
            'city' => 0,
            'state' => 1,
            //'zip' => 7
        );
    }

    public function getEntity($rowData)
    {
        // First, see if there's a matching entity in the db already
        $entity = $this->getCollegeModel()->findOneByIpeds($rowData['ipeds']);

        // If not, create a blank one
        if (empty($entity)) {
            $entity = new CollegeEntity();
        }

        // Now plug in the data
        //$entity->setOpeId($rowData['opeId']);
        $entity->setIpeds($rowData['ipeds']);
        $entity->setName($rowData['name']);
        $entity->setAbbreviation($rowData['name']);
        //$entity->setAddress($rowData['address']);
        $entity->setCity($rowData['city']);
        $entity->setState($rowData['state']);
        //$entity->setZip($rowData['zip']);


        return $entity;
    }

    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function setSystemModel($systemModel)
    {
        $this->systemModel = $systemModel;

        return $this;
    }

    public function getSystemModel()
    {
        return $this->systemModel;
    }

    protected function flush()
    {
        $this->getCollegeModel()->getEntityManager()->flush();
    }

    protected function saveSystem($systemName)
    {
        if (!empty($systemName)) {
            // First, see if the system exists
            $system = $this->getSystemModel()->findByName($systemName);

            // If not, build an empty one
            if (empty($system)) {
                $system = new System();
                $system->setName($systemName);

                $this->getSystemModel()->save($system);
                $this->getSystemModel()->getEntityManager()->flush();
            }

            return $system;
        }
    }
}
