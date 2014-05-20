<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Exceldiff;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;

class ToolController extends AbstractActionController
{
    public function indexAction()
    {

    }

    public function exceldiffAction()
    {
        $fullColumn = 'A';
        $removeColumn = 'B';
        $resultColumn = 'C';
        $startingRow = 2; // Skips header

        $form = new Exceldiff;

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        // Process the file
        if ($request->isPost()) {
            $files = $request->getFiles()->toArray();

            if (empty($files['excel_file']['tmp_name'])) {
                die('missing file.');
            } else {
                $tmpFile = $files['excel_file']['tmp_name'];
                $fileName = $files['excel_file']['name'];

                $excel = PHPExcel_IOFactory::load($tmpFile);
                $sheet = $excel->setActiveSheetIndex(0);

                // Load the emails into arrays
                $emailsToRemove = array();
                $allEmails = array();
                foreach ($sheet->getRowIterator($startingRow) as $row) {
                    /** @var PHPExcel_Worksheet_Row $row */

                    $rowIndex = $row->getRowIndex();

                    // To remove:
                    $email = $sheet->getCell($removeColumn . $rowIndex)->getValue();
                    if (!empty($email)) {
                        $emailsToRemove[] = $email;
                    }

                    $email = $sheet->getCell($fullColumn . $rowIndex)->getValue();
                    if (!empty($email)) {
                        $allEmails[] = $email;
                    }
                }

                // Now do the diff, removing the emails from the full list
                $results = array_diff($allEmails, $emailsToRemove);

                // Cool, now dump these into their own column
                foreach ($sheet->getRowIterator($startingRow) as $row) {
                    /** @var PHPExcel_Worksheet_Row $row */

                    $rowIndex = $row->getRowIndex();
                    $emailToAdd = array_shift($results);

                    $sheet->getCell($resultColumn . $rowIndex)
                        ->setValue($emailToAdd);
                }

                // Label the results row
                $sheet->getCell($resultColumn . '1')->setValue('RESULTS');
                $sheet->getColumnDimension($resultColumn)->setWidth(40);

                // How 'bout a filename?
                $fileName = str_replace('.xls', '-processed.xls', $fileName);

                // Whoop whoop, almost done. Now send the file as a download
                header(
                    'Content-Type: '.
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                );
                header(
                    'Content-Disposition: attachment;filename="' . $fileName . '"'
                );
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $objWriter->save('php://output');

                die;
            }
        }

        return array(
            'form' => $form
        );
    }
}
