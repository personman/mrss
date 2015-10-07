<?php

namespace Mrss\Service;

use Mrss\Form\ImportData;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_Row;
use PHPExcel_IOFactory;

class Import
{
    /** @var PHPExcel $excel */
    protected $excel;

    public function getForm()
    {
        $form = new ImportData('import');

        return $form;
    }

    public function import($filename)
    {
        $start = microtime(true);
        $count =  0;

        $this->excel = $this->openFile($filename);
        $this->excel->setActiveSheetIndex(0);
        $sheet = $this->excel->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            /** @var PHPExcel_Worksheet_Row $row */
            $rowIndex = $row->getRowIndex();

            // Skip the header row
            if ($rowIndex === 1) {
                continue;
            }

            $this->saveRow($row);
            $count++;
        }

        $this->flush();

        $elapsed = round(microtime(true) - $start, 1);
        $stats = "$count records processed in $elapsed seconds.";

        return $stats;
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
}
