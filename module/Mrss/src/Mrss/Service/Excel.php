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

    // Green background for value column
    protected $valueColumnBackground = 'A0F2A3';

    // The Excel column index that holds the db_column
    protected $dbColumnColumn;

    public function getExcelForSubscription(Subscription $subscription)
    {
        $excel = new PHPExcel();
        $this->writeHeaders($excel);
        $this->writeBody($excel, $subscription);

        $this->download($excel);
    }

    /**
     * Export for multiple colleges (a system)
     *
     * @param array $subscriptions
     */
    public function getExcelForSubscriptions($subscriptions)
    {
        $excel = new PHPExcel();
        $this->writeHeadersSystem($excel, $subscriptions);
        $this->writeBodySystem($excel, $subscriptions);

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

    public function writeHeadersSystem(PHPExcel $spreadsheet, $subscriptions)
    {
        $sheet = $spreadsheet->getActiveSheet();

        // Benchmark label
        $sheet->setCellValueByColumnAndRow(0, 1, 'Label');
        $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);

        // Label column align right
        $sheet->getStyle('A1:A' . $this->rowCount)->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $column = 1;
        foreach ($subscriptions as $subscription) {
            $college = $subscription->getCollege();
            $collegeHeader = $college->getName() . " \r("
                . $college->getIpeds() . ')';

            $sheet->setCellValueByColumnAndRow($column, 1, $collegeHeader);
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            $column++;
        }

        $sheet->setCellValueByColumnAndRow($column, 1, 'Data Definition');
        $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        $column++;

        // Hidden column for db_column
        $sheet->setCellValueByColumnAndRow($column, 1, 'Column');
        $sheet->getColumnDimensionByColumn($column)->setVisible(false);

        // Bold header
        $headerRow = $sheet->getStyle('A1:Z1');
        $headerRow->getFont()->setBold(true);

        // Value columns background
        $lastValueColumn = $this->num2alpha(count($subscriptions));
        $sheet->getStyle('B1:' . $lastValueColumn . $this->rowCount)->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setARGB($this->valueColumnBackground);
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

    public function writeBodySystem(PHPExcel $spreadsheet, $subscriptions)
    {
        $exampleSubscription = $subscriptions[0];
        $study = $exampleSubscription->getStudy();
        $year = $study->getCurrentYear();

        $sheet = $spreadsheet->getActiveSheet();

        // Loop over each benchmark, adding a row
        $row = 2;
        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            $benchmarks = $benchmarkGroup->getNonComputedBenchmarksForYear($year);
            foreach ($benchmarks as $benchmark) {
                $this->writeRowSystem($sheet, $row, $benchmark, $subscriptions);
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
    }

    public function writeRowSystem(
        PHPExcel_Worksheet $sheet,
        $row,
        Benchmark $benchmark,
        $subscriptions
    ) {

        // Write the label
        $sheet->setCellValueByColumnAndRow(0, $row, $benchmark->getName());

        // A value field for each subscription
        $column = 1;
        foreach ($subscriptions as $subscription) {
            $value = $subscription->getObservation()->get($benchmark->getDbColumn());
            $sheet->setCellValueByColumnAndRow($column, $row, $value);

            $column++;
        }

        // Write the data definition
        $sheet->setCellValueByColumnAndRow(
            $column,
            $row,
            $benchmark->getDescription()
        );
        $column++;

        // Write the db column
        $sheet->setCellValueByColumnAndRow($column, $row, $benchmark->getDbColumn());
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

    /**
     * Returns an array of arrays, top level keyed by ipeds number, lower level
     * keyed by db_column
     *
     * @param $filename
     * @return array
     * @throws \Exception
     */
    public function getObservationDataFromExcel($filename)
    {
        $excel = $this->openFile($filename);
        $sheet = $excel->getActiveSheet();

        $colleges = $this->getCollegesFromExcel($sheet);
        $data = array();

        foreach ($colleges as $college) {

            $collegeData = array();
            $headerRowSkipped = false;
            foreach ($sheet->getRowIterator() as $row) {
                if (!$headerRowSkipped) {
                    $headerRowSkipped = true;
                    continue;
                }

                $value = $sheet
                    ->getCellByColumnAndRow($college['column'], $row->getRowIndex())
                    ->getValue();

                $dbColumn = $sheet
                    ->getCellByColumnAndRow(
                        $this->dbColumnColumn,
                        $row->getRowIndex()
                    )
                    ->getValue();

                if (empty($dbColumn)) {
                    continue;
                }

                $collegeData[$dbColumn] = $value;
            }

            // If $data is empty, then we're dealing with an invalid file
            if (empty($collegeData)) {
                throw new \Exception('Empty import file');
            }

            $data[$college['ipeds']] = $collegeData;

        }

        return $data;
    }

    /**
     * Find the colleges listed in the Excel header row. Identify them by their IPEDS
     *
     * @param PHPExcel_Worksheet $sheet
     * @return array
     */
    public function getCollegesFromExcel(PHPExcel_Worksheet $sheet)
    {
        $colleges = array();

        $rowIterator = $sheet->getRowIterator(1);
        foreach ($rowIterator as $row) {

            $column = 0;
            foreach ($row->getCellIterator() as $cell) {
                $value = $cell->getValue();

                // Is it a college value column?
                if ($ipeds = $this->extractIpeds($value)) {
                    $colleges[] = array(
                        'ipeds' => $ipeds,
                        'column' => $column
                    );
                }

                if ($value == 'Column') {
                    $this->dbColumnColumn = $column;
                }

                $column++;
            }
            break;
        }

        return $colleges;
    }

    /**
     * Return a six digit ipeds number, if it's there
     *
     * @param $value
     * @return null|array
     */
    public function extractIpeds($value)
    {
        $pattern = '/\d{6}/';
        $ipeds = null;
        preg_match($pattern, $value, $matches);

        if (!empty($matches[0])) {
            $ipeds = $matches[0];
        }

        return $ipeds;
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

    /**
     * Converts an integer into the alphabet base (A-Z).
     *
     * @param int $n This is the number to convert.
     * @return string The converted number.
     * @author Theriault
     *
     */
    public function num2alpha($n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }
}
