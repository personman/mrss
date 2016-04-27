<?php

namespace Mrss\Service\Import;

use Mrss\Entity\Observation as ObservationEntity;
use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\College as CollegeEntity;

class CollegeCategory extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\Observation $observationModel */
    protected $observationModel;

    protected $skipCollegesWithExecTitle = false;

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        $data = $this->getDataFromRow($row);

        if (empty($data['category-int'])) {
            return false;
        }

        if ($college = $this->getCollege($data)) {
            $this->getCollegeModel()->save($college);

            $this->saveObservationData($college, $data);
        }
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
        $map = array(
            'ipeds',
            'opeId',
            'name-ignore',
            'category-ignore',
            'category-int',
        );

        $withNumbers = array();
        $i = 0;

        foreach ($map as $key) {
            $withNumbers[$key] = $i++;
        }

        return $withNumbers;
    }

    public function getCollege($rowData)
    {
        // First, see if there's a matching entity in the db already
        $entity = $this->getCollegeModel()->findOneByIpeds($rowData['ipeds']);

        return $entity;
    }

    protected function saveObservationData($college, $data)
    {
        $observation = $this->createOrUpdateObservation($college);

        $data = $this->mapObservationData($data);

        foreach ($data as $dbColumn => $value) {
            $observation->set($dbColumn, $value);
        }

        $this->getObservationModel()->save($observation);
    }

    protected function mapObservationData($data)
    {
        $newData = array();

        $newData['institution_aaup_category'] = $this->mapCategory($data['category-int']);

        return $newData;
    }

    protected function mapCategory($number)
    {
        $map = array(
            1	=> "Doctoral",
            2	=> "Master's",
            3	=> "Baccalaureate",
            4	=> "Associate's with Ranks",
            5	=> "Associate's without Ranks",
        );

        return $this->mapGeneral($number, $map);
    }

    protected function mapGeneral($number, $map)
    {
        $number = intval($number);

        $result = null;
        if (!empty($map[$number])) {
            $result = $map[$number];
        }

        return $result;
    }

    protected function createOrUpdateObservation($college)
    {
        $year = 2016;
        $observation = $this->getObservationModel()->findOne($college->getId(), $year);

        if (empty($observation)) {
            $observation = new ObservationEntity();
            $observation->setCollege($college);
            $observation->setYear($year);
        }

        return $observation;
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

    public function setObservationModel($model)
    {
        $this->observationModel = $model;

        return $this;
    }

    public function getObservationModel()
    {
        return $this->observationModel;
    }

    public function parseFullName($name)
    {
        $parser = new FullNameParser();

        $parts = $parser->parse_name($name);

        // Tack suffixes onto lastname
        if ($parts['suffix']) {
            $parts['lname'] .= ' ' . $parts['suffix'];
        }

        return $parts;
    }

    protected function flush()
    {
        $this->getCollegeModel()->getEntityManager()->flush();
    }
}
