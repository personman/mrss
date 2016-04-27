<?php

namespace Mrss\Service;

use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_Row;
use PHPExcel_IOFactory;

class Export
{
    public function downloadExcel($excel, $filename)
    {
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }
}
