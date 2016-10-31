<?php

namespace Mrss\Service\Import;

use Mrss\Entity\Observation as ObservationEntity;
use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\College as CollegeEntity;
use Mrss\Service\FullNameParser;

class CollegeDemographics extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\Observation $observationModel */
    protected $observationModel;

    protected $skipIfExecTitle = false;

    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {
        $data = $this->getDataFromRow($row);

        if (empty($data['name'])) {
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
            'name',
            'name2',
            'address',
            'city',
            'state',
            'zip',
            'execName',
            'execTitle',
            'generalPhone', // Not used
            'url', // Not used
            'opeId',
            'sector',
            'control',
            'medical',
            'mergedId', // Not used
            'carnegie',
            'multi'
        );

        $withNumbers = array();
        $iteration = 0;

        foreach ($map as $key) {
            $withNumbers[$key] = $iteration++;
        }

        return $withNumbers;
    }

    public function getCollege($rowData)
    {
        // First, see if there's a matching entity in the db already
        $entity = $this->getCollegeModel()->findOneByIpeds($rowData['ipeds']);

        // If not, create a blank one
        if (empty($entity)) {
            $entity = new CollegeEntity();
        }

        // If this has been imported already, skip it.
        if ($entity->getExecTitle() && $this->skipIfExecTitle) {
        }

        // Now plug in the data
        $entity->setOpeId($rowData['opeId']);
        $entity->setIpeds($rowData['ipeds']);
        $entity->setName($rowData['name']);
        $entity->setAddress($rowData['address']);
        $entity->setCity($rowData['city']);
        $entity->setState($rowData['state']);
        $entity->setZip($rowData['zip']);

        $entity->setExecTitle($rowData['execTitle']);

        if ($nameParts = $this->parseFullName($rowData['execName'])) {
            $entity->setExecSalutation($nameParts['salutation']);
            $entity->setExecFirstName($nameParts['fname']);
            $entity->setExecMiddleName($nameParts['initials']);
            $entity->setExecLastName($nameParts['lname']);
        }

        return $entity;
    }

    protected function saveObservationData($college, $data)
    {
        $observation = $this->createOrUpdateObservation($college);

        $data = $this->mapObservationData($data);

        //prd($data);

        foreach ($data as $dbColumn => $value) {
            $observation->set($dbColumn, $value);
        }

        $this->getObservationModel()->save($observation);
    }

    protected function mapObservationData($data)
    {
        $newData = array();

        // Sector
        $newData['institution_sector'] = $this->mapSector($data['sector']);

        // Control
        $newData['institution_control'] = $this->mapControl($data['control']);

        // Medical
        $newData['institution_grants_medical_degree'] = $this->mapMedical($data['medical']);

        // Carnegie
        $newData['carnegie_basic'] = $this->mapCarnegie($data['carnegie']);

        // Multi
        $newData['institution_campuses'] = $this->mapCampuses($data['multi']);

        return $newData;
    }

    protected function mapSector($number)
    {
        $map = array(
            1	=> "Public, 4-year or above",
            2	=> "Private not-for-profit, 4-year or above",
            3	=> "Private for-profit, 4-year or above",
            4	=> "Public, 2-year",
            5	=> "Private not-for-profit, 2-year",
            6	=> "Private for-profit, 2-year",
        );

        return $this->mapGeneral($number, $map);
    }

    protected function mapControl($number)
    {
        $map = array(
            1	=> "Public",
            2	=> "Private Not-For-profit",
            3	=> "Private for-profit"
        );

        return $this->mapGeneral($number, $map);
    }

    protected function mapMedical($number)
    {
        $map = array(
            1 => 'Yes',
            2 => 'No'
        );

        return $this->mapGeneral($number, $map);
    }

    protected function mapCarnegie($number)
    {
        $map = array(
            1	=> "Associate's--Public Rural-serving Small",
            2	=> "Associate's--Public Rural-serving Medium",
            3	=> "Associate's--Public Rural-serving Large",
            4	=> "Associate's--Public Suburban-serving Single Campus",
            5	=> "Associate's--Public Suburban-serving Multicampus",
            6	=> "Associate's--Public Urban-serving Single Campus",
            7	=> "Associate's--Public Urban-serving Multicampus",
            8	=> "Associate's--Public Special Use",
            9	=> "Associate's--Private Not-for-profit",
            10	=> "Associate's--Private For-profit",
            11	=> "Associate's--Public 2-year colleges under 4-year universities",
            12	=> "Associate's--Public 4-year Primarily Associate's",
            13	=> "Associate's--Private Not-for-profit 4-year Primarily Associate's",
            14	=> "Associate's--Private For-profit 4-year Primarily Associate's",
            15	=> "Research Universities (very high research activity)",
            16	=> "Research Universities (high research activity)",
            17	=> "Doctoral/Research Universities",
            18	=> "Master's Colleges and Universities (larger programs)",
            19	=> "Master's Colleges and Universities (medium programs)",
            20	=> "Master's Colleges and Universities (smaller programs)",
            21	=> "Baccalaureate Colleges--Arts & Sciences",
            22	=> "Baccalaureate Colleges--Diverse Fields",
            23	=> "Baccalaureate/Associate's Colleges",
            24	=> "Theological seminaries, Bible colleges, and other faith-related institutions",
            25	=> "Medical schools and medical centers",
            26	=> "Other health professions schools",
            27	=> "Schools of engineering",
            28	=> "Other technology-related schools",
            29	=> "Schools of business and management",
            30	=> "Schools of art, music, and design",
            31	=> "Schools of law",
            32	=> "Other special-focus institutions",
            33	=> "Tribal Colleges",
            -3	=> "Not applicable, not in Carnegie universe (not accredited or nondegree-granting)"
        );

        return $this->mapGeneral($number, $map);
    }

    protected function mapCampuses($number)
    {
        $map = array(
            1 => 'Institution is part of a multi-institution or multi-campus organization',
            2 => 'Institution is NOT part of a multi-institution or multi-campus organization'
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
