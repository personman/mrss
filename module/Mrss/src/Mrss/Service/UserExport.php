<?php

namespace Mrss\Service;

use \Mrss\Entity\College;
use Mrss\Entity\Study;
use Mrss\Entity\Subscription;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;

/**
 * Class UserExport
 *
 * Export all users with current subscriptions
 *
 * @package Mrss\Service
 */
class UserExport
{
    protected $row = 1;

    /**
     * @var PHPExcel $excel
     */
    protected $excel;
    protected $filename = 'user-export';
    protected $study;
    protected $subscriptionModel;
    protected $collegeModel;

    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    public function setSubscriptionModel($subscriptionModel)
    {
        $this->subscriptionModel = $subscriptionModel;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;
    }

    /**
     * @return \Mrss\Model\College
     */
    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function export($year = null)
    {
        if (empty($this->study)) {
            throw new \Exception("Study cannot be empty. Please use setStudy().");
        }

        // Start building the Excel file
        $this->excel = new PHPExcel();
        $this->writeHeaders();
        $this->writeData($year);

        $this->download();
    }

    protected function writeHeaders()
    {
        $sheet = $this->excel->getActiveSheet();
        $row = $this->row;

        $sheet->setCellValue('A' . $row, 'Institution');
        $sheet->setCellValue('B' . $row, 'IPEDS');
        $sheet->setCellValue('C' . $row, 'Address');
        $sheet->setCellValue('D' . $row, 'Address2');
        $sheet->setCellValue('E' . $row, 'City');
        $sheet->setCellValue('F' . $row, 'State');
        $sheet->setCellValue('G' . $row, 'Zip');

        // User data
        $sheet->setCellValue('H' . $row, 'Prefix');
        $sheet->setCellValue('I' . $row, 'First Name');
        $sheet->setCellValue('J' . $row, 'Last Name');
        $sheet->setCellValue('K' . $row, 'Title');
        $sheet->setCellValue('L' . $row, 'Email');
        $sheet->setCellValue('M' . $row, 'Phone');
        $sheet->setCellValue('N' . $row, 'Extension');
        $sheet->setCellValue('O' . $row, 'Role');
        $sheet->setCellValue('P' . $row, 'Years');

        $column = 'P';
        // Section
        if ($this->getStudy()->hasSections()) {
            foreach ($this->getStudy()->getSections() as $section) {
                $column++;
                $sheet->setCellValue($column . $row, $section->getName());
            }
        }

        // Style 'em
        $headerRow = $sheet->getStyle('A1:' . $column . '1');
        $headerRow->getFont()->setBold(true);
    }

    /**
     * @return \Mrss\Entity\College[]
     */
    protected function getColleges()
    {
        return $this->getCollegeModel()->findAll();
    }

    protected function writeData($year = null)
    {
        $this->row = 2;

        if ($year) {
            $subscriptions = $this->getSubscriptions($year);
            foreach ($subscriptions as $subscription) {
                $college = $subscription->getCollege();
                $this->writeRowsForCollege($college, $subscription);
            }
        } else {
            foreach ($this->getColleges() as $college) {
                $this->writeRowsForCollege($college);
            }
        }


        // Autosize
        $sheet = $this->excel->getActiveSheet();
        foreach (range(0, 19) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutosize(true);
        }
    }

    /**
     * @param College $college
     * @param null|Subscription $subscription
     */
    protected function writeRowsForCollege(College $college, $subscription = null)
    {
        $sheet = $this->excel->getActiveSheet();

        $users = $college->getUsersByStudy($this->getStudy());

        foreach ($users as $user) {
            $row = $this->row;

            // College data
            $sheet->setCellValue('A' . $row, $college->getName());
            $sheet->setCellValue('B' . $row, $college->getIpeds());
            $sheet->setCellValue('C' . $row, $college->getAddress());
            $sheet->setCellValue('D' . $row, $college->getAddress2());
            $sheet->setCellValue('E' . $row, $college->getCity());
            $sheet->setCellValue('F' . $row, $college->getState());
            $sheet->setCellValue('G' . $row, $college->getZip());

            // User data
            $sheet->setCellValue('H' . $row, $user->getPrefix());
            $sheet->setCellValue('I' . $row, $user->getFirstName());
            $sheet->setCellValue('J' . $row, $user->getLastName());
            $sheet->setCellValue('K' . $row, $user->getTitle());
            $sheet->setCellValue('L' . $row, $user->getEmail());
            $sheet->setCellValue('M' . $row, $user->getPhone());
            $sheet->setCellValue('N' . $row, $user->getExtension());
            $sheet->setCellValue('O' . $row, $user->getRole());
            $sheet->setCellValue('P' . $row, implode(',', $college->getYears()));

            $column = 'P';
            // Section
            if ($subscription && $this->getStudy()->hasSections()) {
                foreach ($this->getStudy()->getSections() as $section) {
                    $hasSection = '';
                    if ($subscription->hasSection($section)) {
                        $hasSection = '1';
                    }
                    $column++;
                    $sheet->setCellValue($column . $row, $hasSection);
                }
            }

            $this->row++;
        }
    }

    /**
     * @param $year
     * @return \Mrss\Entity\Subscription[]
     */
    protected function getSubscriptions($year)
    {
        if (empty($year)) {
            $year = $this->getStudy()->getCurrentYear();
        }

        $studyId = $this->getStudy()->getId();

        $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear(
            $studyId,
            $year
        );

        return $subscriptions;
    }

    protected function download()
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
}
