<?php

namespace Mrss\Service;

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
    /**
     * @var PHPExcel $excel
     */
    protected $excel;
    protected $filename = 'user-export';
    protected $study;
    protected $subscriptionModel;

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

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
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
        $row = 1;

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

        $column = 'O';
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

    protected function writeData($year)
    {
        $subscriptions = $this->getSubscriptions($year);
        $sheet = $this->excel->getActiveSheet();

        $row = 2;
        foreach ($subscriptions as $subscription) {
            /** @var \Mrss\Entity\Subscription $subscription */
            $college = $subscription->getCollege();
            $users = $college->getUsersByStudy($this->getStudy());

            foreach ($users as $user) {
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

                $column = 'O';
                // Section
                if ($this->getStudy()->hasSections()) {
                    foreach ($this->getStudy()->getSections() as $section) {
                        $hasSection = '';
                        if ($subscription->hasSection($section)) {
                            $hasSection = '1';
                        }
                        $column++;
                        $sheet->setCellValue($column . $row, $hasSection);
                    }
                }

                $row++;
            }
        }

        // Autosize
        foreach (range(0, 16) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutosize(true);
        }
    }

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
