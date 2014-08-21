<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use Mrss\Entity\Subscription;
use Mrss\Entity\Benchmark;
use Mrss\Model\Benchmark as BenchmarkModel;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;
use PHPExcel_Cell;

class Excel
{
    // Maximum row count
    protected $rowCount = 160;

    // Green background for value column
    protected $valueColumnBackground = 'A0F2A3';

    // The Excel column index that holds the db_column
    protected $dbColumnColumn;

    // Which row holds the header?
    protected $headerRowIndex = 1;

    // Current study
    /** @var \Mrss\Entity\Study */
    protected $currentStudy;

    // Current college
    /** @var \Mrss\Entity\College */
    protected $currentCollege;

    /** @var  \Mrss\Model\Benchmark */
    protected $benchmarkModel;

    /**
     * @deprecated
     * @param Subscription $subscription
     */
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
     * @param Subscription[] $subscriptions
     */
    public function getExcelForSubscriptions($subscriptions)
    {
        $excel = new PHPExcel();
        $this->writeHeadersSystem($excel, $subscriptions);
        $this->writeBodySystem($excel, $subscriptions);

        // Per-study export customization
        if (count($subscriptions) == 1) {
            $subscription = $subscriptions[0];
            if ($subscription->getStudy()->getId() == 2) {
                $this->customizeForMrss($excel, $subscription);
            }

            if ($subscription->getStudy()->getId() == 3) {
                $this->customizeForWorkforce($excel, $subscription);
            }
        }


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

    /**
     * @param PHPExcel $spreadsheet
     * @param Subscription[] $subscriptions
     */
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

    /**
     * @param PHPExcel $spreadsheet
     * @param Subscription[] $subscriptions
     */
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

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param $row
     * @param Benchmark $benchmark
     * @param Subscription $subscription
     */
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

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param $row
     * @param Benchmark $benchmark
     * @param Subscription[] $subscriptions
     */
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
        //$excel->setActiveSheetIndexByName('Worksheet');
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();

        // For testing whether a dbColumn is valid
        $emptyObservation = new Observation();
        $emptySubObservation = new SubObservation();

        $this->applyImportCustomizations();

        $colleges = $this->getCollegesFromExcel($sheet);
        $data = array();

        foreach ($colleges as $college) {

            $collegeData = array();
            $headerRowSkipped = false;
            foreach ($sheet->getRowIterator() as $row) {
                /** @var PHPExcel_Worksheet_Row $row */

                if (!$headerRowSkipped) {
                    $headerRowSkipped = true;
                    continue;
                }

                $dbColumn = $sheet
                    ->getCellByColumnAndRow(
                        $this->dbColumnColumn,
                        $row->getRowIndex()
                    )
                    ->getValue();

                // Skip empty rows
                if (empty($dbColumn)) {
                    continue;
                }

                $value = $sheet
                    ->getCellByColumnAndRow($college['column'], $row->getRowIndex())
                    ->getCalculatedValue();

                // Observation data
                if ($emptyObservation->has($dbColumn)) {
                    $collegeData[$dbColumn] = $value;
                } elseif (list($subObIndex, $dbColumn) = $this->getSubObIndex(
                    $dbColumn,
                    $emptySubObservation
                )
                ) {
                    if (empty($collegeData['subobservations'])) {
                        $collegeData['subobservations'] = array();
                    }
                    if (empty($collegeData['subobservations'][$subObIndex])) {
                        $collegeData['subobservations'][$subObIndex] = array();
                    }

                    $collegeData['subobservations'][$subObIndex][$dbColumn] = $value;
                }
            }

            // Trim empty subobservations
            if (!empty($collegeData['subobservations'])) {
                foreach ($collegeData['subobservations'] as $key => $subOb) {
                    // Name is the required field
                    if (empty($subOb['name'])) {
                        unset($collegeData['subobservations'][$key]);
                    }
                }
            }

            // If $data is empty, then we're dealing with an invalid file
            if (empty($collegeData)) {
                throw new \Exception('Empty import file');
            }

            $data[$college['ipeds']] = $collegeData;

        }

        return $data;
    }

    protected function getSubObIndex($dbColumn, SubObservation $emptySubOb)
    {
        // @todo: This probably shouldn't be hardcoded as it applies only to MRSS
        // form 2
        $subObPrefix = 'inst_cost_';

        // Extract the number from the field
        $pattern = '/u(\d+)_(.*)/';
        preg_match($pattern, $dbColumn, $matches);

        if (!empty($matches[1])) {
            $index = $matches[1];
            if ($matches[2] == 'unit_name') {
                $shortColumn = 'name';
            } else {
                $shortColumn = $subObPrefix . $matches[2];
            }

            if ($emptySubOb->has($shortColumn) || $shortColumn == 'unit_name') {
                return array($index, $shortColumn);
            }
        }

        return false;
    }

    protected function applyImportCustomizations()
    {
        // Workforce import customizations
        if ($this->getCurrentStudy()->getId() == 3) {
            $this->headerRowIndex = 5;
        }
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

        $rowIterator = $sheet->getRowIterator($this->headerRowIndex);
        foreach ($rowIterator as $row) {
            /** @var PHPExcel_Worksheet_Row $row */

            $column = 0;
            foreach ($row->getCellIterator() as $cell) {
                /** @var PHPExcel_Cell $cell */

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

        // If none are found in the file, just use the current college
        if (empty($colleges)) {
            $college = $this->getCurrentCollege();
            $colleges[] = array(
                'ipeds' => $college->getIpeds(),
                'column' => 1
            );
            $this->dbColumnColumn = 3;
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

        //var_dump($filename);
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

    protected function customizeForWorkforce(
        PHPExcel $spreadsheet,
        Subscription $subscription
    ) {
        // Open the template file
        $filename = 'data/imports/workforce.xlsx';
        $excel = PHPExcel_IOFactory::load($filename);

        // Remove the old sheet
        $spreadsheet->removeSheetByIndex(0);

        // Place the sheet
        $sheetTitle = 'Worksheet';
        $sheet = clone $excel->getSheetByName($sheetTitle);
        $sheet->setTitle($sheetTitle);
        $spreadsheet->addExternalSheet($sheet, 0);
        $spreadsheet->setActiveSheetIndex(0);

        // Populate the values
        $this->populateWorkforceValues($spreadsheet, $subscription);

        // Set the college name
        $college = $subscription->getCollege();
        $collegeHeader = $college->getName() . " \r("
            . $college->getIpeds() . ')';
        $spreadsheet->getActiveSheet()->setCellValue('B5', $collegeHeader);
    }

    protected function populateWorkforceValues(PHPExcel $spreadsheet, Subscription $subscription)
    {
        // Loop over the rows and insert the observation value based on the dbColumn
        // in col D
        $sheet = $spreadsheet->getActiveSheet();
        $valueColumn = 'B';
        $dbColumnCol = 'D';
        $observation = $subscription->getObservation();

        foreach ($sheet->getRowIterator() as $row) {
            /** @var PHPExcel_Worksheet_Row $row */

            $rowIndex = $row->getRowIndex();

            $rowDbcolumn = $sheet->getCell($dbColumnCol . $rowIndex)->getValue();

            if ($rowDbcolumn && $observation->has($rowDbcolumn)) {
                $value = $observation->get($rowDbcolumn);
                $sheet->setCellValue($valueColumn . $rowIndex, $value);
            }
        }
    }

    protected function customizeForMrss(PHPExcel $spreadsheet, Subscription $subscription)
    {
        // Discard the generic version
        unset($spreadsheet);

        // Open the template Excel  file
        $filename = 'data/imports/mrss-export.xlsx';
        $spreadsheet = PHPExcel_IOFactory::load($filename);

        $this->populateMrss($spreadsheet, $subscription);

        // Activate the first sheet
        $spreadsheet->setActiveSheetIndex(0);


        $this->download($spreadsheet);

    }

    protected function populateMrss(PHPExcel $spreadsheet, Subscription $subscription)
    {
        $valueColumn = 'B';
        $dbColumnCol = 'D';
        $definitionCol = 'C';
        $firstHiddenRow = 250;
        $lastHiddenRow = 497;

        $observation = $subscription->getObservation();

        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            /** @var PHPExcel_Worksheet_Row $row */

            $rowIndex = $row->getRowIndex();
            $dbColumn = $sheet->getCell($dbColumnCol . $rowIndex)->getValue();

            if (!empty($dbColumn) && $observation->has($dbColumn)) {
                // Populate the value
                $value = $observation->get($dbColumn);
                $sheet->setCellValue($valueColumn . $rowIndex, $value);

                // Populate the description
                if (false) {
                    $benchmark = $this->getBenchmarkModel()
                        ->findOneByDbColumnAndStudy(
                            $dbColumn,
                            $this->getCurrentStudy()->getId()
                        );
                    $definition = strip_tags($benchmark->getDescription());
                    $sheet->setCellValue($definitionCol . $rowIndex, $definition);
                }
            }
        }

        // Now hide some stuff
        $sheet->getColumnDimension($dbColumnCol)->setVisible(false);
        $sheet->getColumnDimension('E')->setVisible(false);
        $sheet->getColumnDimension('F')->setVisible(false);

        foreach (range($firstHiddenRow, $lastHiddenRow) as $rowIndex) {
            $sheet->getRowDimension($rowIndex)->setVisible(false);
        }

        // Handle subobservations

        $this->populateMrssSubObservations(
            $spreadsheet,
            $observation
        );

    }

    protected function populateMrssSubObservations(
        PHPExcel $spreadsheet,
        Observation $observation
    ) {
        $subObSheets = range(1, 10);

        $i = 0;
        foreach ($observation->getSubObservations() as $subOb) {
            $sheetIndex = $subObSheets[$i];
            $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
            $this->populateMrssSubObservation($sheet, $subOb);

            $i++;
        }

        /* Victoria has these up to date in the excel file
        // Set data definitions
        foreach ($subObSheets as $index) {
            $sheet = $spreadsheet->setActiveSheetIndex($index);
            // Some descriptions
            foreach ($this->getMrssSubObservationDefinitionMap() as $cell => $dbColumn) {
                // Description
                $benchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn);
                if ($benchmark) {
                    $definition = $benchmark->getDescription();
                    $sheet->setCellValue($cell, $definition);
                }
            }

        }
        */
    }

    protected function populateMrssSubObservation(
        PHPExcel_Worksheet $sheet,
        SubObservation $subObservation
    ) {
        // Set the academic unit name
        $sheet->setCellValue('A10', $subObservation->getName());

        // Now the data
        foreach ($this->getMrssSubObservationMap() as $cell => $dbColumn) {
            $value = $subObservation->get($dbColumn);
            $sheet->setCellValue($cell, $value);
        }
    }

    protected function getMrssSubObservationMap()
    {
        return array(
            'C13' => 'inst_cost_full_expend',
            'C14' => 'inst_cost_full_num',
            'C15' => 'inst_cost_full_cred_hr',
            'C17' => 'inst_cost_part_num',
            'C18' => 'inst_cost_part_cred_hr',
            'C19' => 'inst_cost_part_cred_hr',
            'B27' => 'inst_cost_full_program_dev',
            'C27' => 'inst_cost_part_program_dev',
            'B28' => 'inst_cost_full_course_dev',
            'C28' => 'inst_cost_part_course_dev',
            'B29' => 'inst_cost_full_teaching',
            'C29' => 'inst_cost_part_teaching',
            'B30' => 'inst_cost_full_tutoring',
            'C30' => 'inst_cost_part_tutoring',
            'B31' => 'inst_cost_full_advising',
            'C31' => 'inst_cost_part_advising',
            'B32' => 'inst_cost_full_ac_service',
            'C32' => 'inst_cost_part_ac_service',
            'B33' => 'inst_cost_full_assessment',
            'C33' => 'inst_cost_part_assessment',
            'B34' => 'inst_cost_full_prof_dev',
            'C34' => 'inst_cost_part_prof_dev'
        );
    }

    protected function getMrssSubObservationDefinitionMap()
    {
        return array(
            'D9' => 'inst_cost_full_expend',
            'D10' => 'inst_cost_full_num',
            'D11' => 'inst_cost_full_cred_hr',
            'D16' => 'inst_cost_part_num',
            'D17' => 'inst_cost_part_cred_hr',
            'D18' => 'inst_cost_part_cred_hr',
        );
    }

    protected function customizeForMrssOld(PHPExcel $spreadsheet, Subscription $subscription)
    {
        // Open the template Excel  file
        $filename = 'data/imports/mrss-grid.xlsx';
        $gridTemplate = PHPExcel_IOFactory::load($filename);

        // Copy the grid sheet into the export file
        $sheetTitle = 'Instructional';
        $sheet = clone $gridTemplate->getSheetByName($sheetTitle);
        $sheet->setTitle($sheetTitle);
        $spreadsheet->addExternalSheet($sheet, 0);
        $spreadsheet->setActiveSheetIndex(0);

        // Populate the grid
        $this->populateMrssGrid($spreadsheet, $subscription);

        // Connect to cells in the other sheet
        $this->connectMrssGridCells($spreadsheet, $subscription);

        // Hide those other sheet rows
    }

    protected function populateMrssGrid(PHPExcel $spreadsheet, Subscription $subscription)
    {
        $map = $this->getMrssGridMap();
        $sheet = $spreadsheet->getActiveSheet();
        $observation = $subscription->getObservation();

        $currentRow = 8;
        foreach ($map as $row) {
            $currentCol = 5;

            foreach ($row as $dbColumn) {
                $value = $observation->get($dbColumn);
                // The grid wants a decimal value
                $value = $value / 100;
                $sheet->setCellValueByColumnAndRow($currentCol, $currentRow, $value);

                $currentCol++;
            }

            $currentRow++;
        }
    }

    protected function connectMrssGridCells(PHPExcel $spreadsheet)
    {
        $sheetName = 'Instructional';

        $currentRow = 8;
        foreach ($this->getMrssGridMap() as $row) {
            $currentCol = 5;

            foreach ($row as $dbColumn) {
                $columnLetter = $this->num2alpha($currentCol);
                $reference = '=100*' . $sheetName . '!' . $columnLetter . $currentRow;

                $this->placeReference($spreadsheet, $reference, $dbColumn);

                $currentCol++;
            }

            $currentRow++;
        }
    }

    protected function placeReference(PHPExcel $spreadsheet, $reference, $dbColumn)
    {
        $sheetName = 'Worksheet';
        $sheet = $spreadsheet->getSheetByName($sheetName);

        // Loop over the rows to find the one that needs this reference
        foreach ($sheet->getRowIterator() as $row) {
            /** @var PHPExcel_Worksheet_Row $row */

            $dbColumnCol = 'D';
            $rowIndex = $row->getRowIndex();

            $rowDbcolumn = $sheet->getCell($dbColumnCol . $rowIndex)->getValue();

            if ($rowDbcolumn == $dbColumn) {
                // We're in the right row
                $valueCol = 'B';

                // Set the reference
                $sheet->setCellValue($valueCol . $rowIndex, $reference);

                // Now hide the row so they only edit it in one place
                $sheet->getRowDimension($rowIndex)->setVisible(false);

                return true;
            }
        }

        return false;
    }

    protected function getMrssGridMap()
    {
        return array(
            array(
                'inst_full_program_dev',
                'inst_part_program_dev',
                'inst_othr_program_dev'
            ),
            array(
                'inst_full_course_dev',
                'inst_part_course_dev',
                'inst_othr_course_dev'
            ),
            array(
                'inst_full_teaching',
                'inst_part_teaching',
                'inst_othr_teaching'
            ),
            array(
                'inst_full_tutoring',
                'inst_part_tutoring',
                'inst_othr_tutoring'
            ),
            array(
                'inst_full_advising',
                'inst_part_advising',
                'inst_othr_advising'
            ),
            array(
                'inst_full_ac_service',
                'inst_part_ac_service',
                'inst_othr_ac_service'
            ),
            array(
                'inst_full_assessment',
                'inst_part_assessment',
                'inst_othr_assessment'
            ),
            array(
                'inst_full_other',
                'inst_part_other',
                'inst_othr_other'
            )
        );
    }

    public function setCurrentStudy($study)
    {
        $this->currentStudy = $study;

        return $this;
    }

    public function getCurrentStudy()
    {
        return $this->currentStudy;
    }

    public function setCurrentCollege($college)
    {
        $this->currentCollege = $college;

        return $this;
    }

    public function getCurrentCollege()
    {
        return $this->currentCollege;
    }

    public function setBenchmarkModel(BenchmarkModel $model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }
}
