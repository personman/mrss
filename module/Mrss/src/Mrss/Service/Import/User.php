<?php

namespace Mrss\Service\Import;

use Mrss\Entity\System;
use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\College as CollegeEntity;
use Mrss\Entity\User as UserEntity;

class User extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\User $userModel */
    protected $userModel;

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        die('not yet implemented');
        $data = $this->getDataFromRow($row);

        if (empty($data['name'])) {
            return false;
        }

        $entity = $this->getEntity($data);

        if ($system = $this->saveSystem($data['systemName'])) {
            $entity->setSystem($system);
        }

        $this->getCollegeModel()->save($entity);
    }

    protected function getDataFromRow(PHPExcel_Worksheet_Row $row)
    {
        $rowIndex = $row->getRowIndex();

        $rowData = array();
        foreach ($this->getMap() as $property => $column) {
            $rowData[$property] = $this->excel->getActiveSheet()->getCellByColumnAndRow($column, $rowIndex)->getValue();
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
            'opeId' => 0,
            'ipeds' => 1,
            'systemName' => 2,
            'name' => 3,
            'address' => 4,
            'city' => 5,
            'state' => 6,
            'zip' => 7
        );
    }

    public function getEntity($rowData)
    {
        // First, see if there's a matching entity in the db already
        $entity = $this->getCollegeModel()->findOneByOpeId($rowData['opeId']);

        // If not, create a blank one
        if (empty($entity)) {
            $entity = new CollegeEntity();
        }

        // Now plug in the data
        $entity->setOpeId($rowData['opeId']);
        $entity->setIpeds($rowData['ipeds']);
        $entity->setName($rowData['name']);
        $entity->setAddress($rowData['address']);
        $entity->setCity($rowData['city']);
        $entity->setState($rowData['state']);
        $entity->setZip($rowData['zip']);

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

    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;

        return $this;
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    protected function flush()
    {
        $this->getCollegeModel()->getEntityManager()->flush();
    }
}
