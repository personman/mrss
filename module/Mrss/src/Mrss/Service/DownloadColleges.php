<?php

namespace Mrss\Service;

use PHPExcel;
use PHPExcel_Style_Alignment;

/**
 * Class DownloadColleges
 *
 * Note: this is not the code that powers the NCCBP national report member list.
 * That's in SubscriptionController
 *
 * @package Mrss\Service
 */
class DownloadColleges extends Report
{
    protected $study;

    protected $collegeModel;

    protected $excel;

    protected $row = 1;

    public function download()
    {
        $filename = 'full-institution-list';

        $this->initializeExportFile();
        $this->writeHeaders();
        $this->writeBody();
        $this->setAutosize();

        $this->downloadExcel($this->excel, $filename);
    }

    protected function initializeExportFile()
    {
        $this->excel = new PHPExcel();
    }

    protected function writeHeaders()
    {
        $sheet = $this->excel->getActiveSheet();

        // Headers


        $sheet->fromArray($this->getHeaders(), null, 'A1');
        $this->row++;
    }

    protected function getHeaders()
    {
        $headers = array(
            'IPEDS ID',
            'OPE ID',
            'Institution Name',
            'Years Participating',
            'Users',
            'Address',
        );

        return $headers;
    }

    public function writeBody()
    {
        $sheet = $this->excel->getActiveSheet();

        foreach ($this->getCollegeModel()->findAll() as $college) {
            $subs = $college->getSubscriptionsForStudy($this->getStudy());
            $yearsParticipating = array_keys($subs);
            asort($yearsParticipating);
            $yearsParticipating = implode(', ', $yearsParticipating);

            $users = $college->getUsersByStudy($this->study);
            $userStrings = array();
            foreach ($users as $user) {
                $userStrings[] = $user->getFullName() . ' <' . $user->getEmail() . '>';
            }
            $userStrings = implode(', ', $userStrings);

            $row = array(
                $college->getIpeds(),
                $college->getOpeId(),
                $college->getNameAndState(),
                $yearsParticipating,
                $userStrings,
                $college->getFullAddress(", ")
            );

            $sheet->fromArray($row, null, 'A' . $this->row);


            $rowCoordinates = 'A' . $this->row . ':' . 'F' . $this->row;
            $sheet->getStyle($rowCoordinates)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $this->row++;
        }
    }

    public function setAutosize()
    {
        $sheet = $this->excel->getActiveSheet();

        $colCount = count($this->getHeaders());

        foreach (range(0, $colCount - 1) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }
    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }
}
