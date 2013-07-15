<?php

namespace Mrss\Service;

use Mrss\Entity\Subscription;

use Mrss\Entity\Benchmark;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;

class Excel
{
    // Maximum row count
    protected $rowCount = 160;

    public function getExcelForSubscription(Subscription $subscription)
    {
        $excel = new PHPExcel();
        $this->writeHeaders($excel);
        $this->writeBody($excel, $subscription);

        $this->download($excel);
    }

    public function writeHeaders(PHPExcel $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Label');
        $sheet->setCellValue('B1', 'Value');
        $sheet->setCellValue('C1', 'Data Definition');
        //$sheet->setCellValue('D1', 'Column');

        // Hide column D
        $sheet->getColumnDimension('D')->setVisible(false);

        // Some formatting:
        // Autosize
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        // Header row
        $headerRow = $sheet->getStyle('A1:C1');
        $headerRow->getFont()->setBold(true);

        // Label column align right
        $sheet->getStyle('A1:A' . $this->rowCount)->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Value column background
        $sheet->getStyle('B1:B' . $this->rowCount)->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setARGB(\PHPExcel_Style_Color::COLOR_GREEN);

    }

    public function writeBody(PHPExcel $spreadsheet, Subscription $subscription)
    {
        $year = $subscription->getStudy()->getCurrentYear();
        $sheet = $spreadsheet->getActiveSheet();

        // Loop over each benchmark, adding a row
        $row = 2;
        foreach ($subscription->getStudy()->getBenchmarkGroups() as $benchmarkGroup) {
            $benchmarks = $benchmarkGroup->getNonComputedBenchmarksForYear($year);
            foreach ($benchmarks as $benchmark) {
                $this->writeRow($sheet, $row, $benchmark, $subscription);
                $row++;
            }
        }
    }

    public function writeRow(PHPExcel_Worksheet $sheet, $row, Benchmark $benchmark, $subscription)
    {
        // Write the label
        $sheet->setCellValue('A' . $row, $benchmark->getName());

        // Write the value
        $value = $subscription->getObservation()->get($benchmark->getDbColumn());
        $sheet->setCellValue('B' . $row, $value);

        // Write the description/definition
        $sheet->setCellValue('C' . $row, $benchmark->getDescription());

        // Write the db column
        $sheet->setCellValue('D' . $row, $benchmark->getDbColumn());

        //
    }

    public function download($spreadsheet)
    {
        // redirect output to client browser
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="data-export.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    public function getObservationDataFromExcel($filename)
    {
        $valueColumn = 1;
        $dbColumnColumn = 3;

        $excel = $this->openFile($filename);
        $sheet = $excel->getActiveSheet();

        $data = array();
        $headerRowSkipped = false;
        foreach ($sheet->getRowIterator() as $row) {
            if (!$headerRowSkipped) {
                $headerRowSkipped = true;
                continue;
            }

            $value = $sheet
                ->getCellByColumnAndRow($valueColumn, $row->getRowIndex())
                ->getValue();

            $dbColumn = $sheet
                ->getCellByColumnAndRow($dbColumnColumn, $row->getRowIndex())
                ->getValue();

            if (empty($dbColumn)) {
                continue;
            }

            $data[$dbColumn] = $value;
        }

        // If $data is empty, then we're dealing with an invalid file
        if (empty($data)) {
            throw new \Exception('Empty import file');
        }

        return $data;
    }

    public function openFile($filename)
    {
        // Check the file format first
        $filetype = PHPExcel_IOFactory::identify($filename);
        //var_dump($filetype); die;
        switch ($filetype) {
            case 'Excel2007':
            case 'Excel2003XML':
            case 'Excel5':
            case 'OOCalc':
            case 'SYLK':
                break;
            default:
                throw new \Exception('Invalid file type');
                break;
        }

        $excel = PHPExcel_IOFactory::load($filename);

        return $excel;
    }
}
