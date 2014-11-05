<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Exceldiff;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;
use Mrss\Service\NccbpMigration;
use Zend\Session\Container;


class ToolController extends AbstractActionController
{
    public function indexAction()
    {
        return array(
            'gc_lifetime' => ini_get('session.gc_maxlifetime'),
            'remember_me_seconds' => ini_get('session.remember_me_seconds')
        );
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

    public function geocodeAction()
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $subscriptionModel = $this->getServiceLocator()
            ->get('model.college');


        $colleges = $collegeModel->findAll();
        $attempt = $found = 0;
        $notFound = array();
        foreach ($colleges as $college) {
            if ($college->getLatitude()) {
                continue;
            }


            $address = $college->getFullAddress();
            $address = str_replace('<br>', ',', $address);
            $geo = $this->geocode($address);
            $attempt++;

            if ($geo) {
                $college->setLatitude($geo['lat']);
                $college->setLongitude($geo['lon']);

                $collegeModel->save($college);
                $found++;
            } else {
                $notFound[] = "Not found: " . $college->getName() . "<br>" .
                    $address;
            }
        }

        $collegeModel->getEntityManager()->flush();

        $message = "$found colleges geocoded (of $attempt attempts). <br>";
        $message .= implode('<br><br>', $notFound);

        $this->flashMessenger()->addSuccessMessage($message);

        return $this->redirect()->toUrl('/tools');
    }

    public function nccbpReportAuditAction()
    {
        $service = new NccbpMigration();

        $response = $service->getOldReport();

        return array(
            'responseBody' => $response->getBody()
        );
    }

    public function geocode($address)
    {
        $address = urlencode($address);
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($ch);
        $info = json_decode($json, true);

        if ($info['status'] == 'OK') {
            $lat = $info['results'][0]['geometry']['location']['lat'];
            $lon = $info['results'][0]['geometry']['location']['lng'];

            return array(
                'lat' => $lat,
                'lon' => $lon
            );
        } else {
            //var_dump($address);
            //var_dump($info);
        }

        return false;
    }
}
