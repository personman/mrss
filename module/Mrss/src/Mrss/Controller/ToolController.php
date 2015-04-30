<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Exceldiff;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;
use PHPExcel_Style_Fill;
use Mrss\Service\NccbpMigration;
use Zend\Session\Container;

class ToolController extends AbstractActionController
{
    public function indexAction()
    {
        $baseTime = round(microtime(1) - REQUEST_MICROTIME, 3);

        return array(
            'gc_lifetime' => ini_get('session.gc_maxlifetime'),
            'cookie_lifetime' => ini_get('session.cookie_lifetime'),
            'remember_me_seconds' => ini_get('session.remember_me_seconds'),
            'baseTime' => $baseTime,
            'collegesWithNoExec' => $this->getMembersWithNoExec()
        );
    }

    public function getMembersWithNoExec()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();

        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // If reports aren't open yet, show the previous year
            if (!$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }
        }

        $subscriptions = $subscriptionModel->findByStudyAndYear(
            $studyId,
            $year
        );

        $collegesWithNoExec = array();
        foreach ($subscriptions as $subscription) {
            $college = $subscription->getCollege();
            if (!$college->getExecLastName()) {
                $collegesWithNoExec[] = $college;
            }
        }

        return $collegesWithNoExec;
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

    public function calcCompletionAction()
    {
        $this->longRunningScript();

        $start = microtime(1);
        $subscriptions = 0;

        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        // Loop over all subscriptions
        foreach ($study->getSubscriptions() as $subscription) {
            $observation = $subscription->getObservation();
            if (empty($observation)) {
                continue;
            }

            // Debug
            //if ($subscription->getYear() != 2010) continue;

            $completion = $study->getCompletionPercentage(
                $observation
            );

            $subscription->setCompletion($completion);
            $subscriptionModel->save($subscription);

            $subscriptions++;
        }

        $subscriptionModel->getEntityManager()->flush();

        $elapsed = round(microtime(1) - $start, 3);
        $this->flashMessenger()
            ->addSuccessMessage("$subscriptions processed in $elapsed seconds.");
        return $this->redirect()->toRoute('tools');

    }

    public function bestAction()
    {
        $benchmarkGroups = $this->currentStudy()->getBenchmarkGroups();
        return array(
            'benchmarkGroups' => $benchmarkGroups
        );
    }

    public function execAddressesAction()
    {
        // Start the excel file
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Header row
        $header = array(
            'Executive Full Name',
            'Executive Title',
            'Executive Prefix',
            'Executive Last Name',
            'Institution Name',
            'Address',
            'Address2',
            'City',
            'State',
            'Zip'
        );

        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            )
        );

        $sheet->fromArray($header, null, 'A' . $row);
        $sheet->getStyle("A$row:J$row")->applyFromArray($blueBar);
        $row++;

        // Get the subscriptions
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();

        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // If reports aren't open yet, show the previous year
            if (!$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }
        }

        $filename = 'exec-report-addresses-' . $year;

        $subscriptions = $subscriptionModel->findByStudyAndYear(
            $studyId,
            $year
        );

        // Exclude some for 2014:
        $ipedsToExclude = array(
            '217989',
            '146603',
            '020774',
            '107992'
        );

        foreach ($subscriptions as $subscription) {
            $college = $subscription->getCollege();

            if (in_array($college->getIpeds(), $ipedsToExclude)) {
                continue;
            }


            $dataRow = array(
                $college->getExecFullName(),
                $college->getExecTitle(),
                $college->getExecSalutation(),
                $college->getExecLastName(),
                $college->getName(),
                $college->getAddress(),
                $college->getAddress2(),
                $college->getCity(),
                $college->getState(),
                $college->getZip()
            );

            $sheet->fromArray($dataRow, null, 'A' . $row);
            $row++;
        }

        // Some formatting
        foreach (range(0, count($header) - 1) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    public function copyDataAction()
    {
        // Copy one year's data for the study to another. dangerous
        $from = $this->params()->fromRoute('from');
        $to = $this->params()->fromRoute('to');

        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        /** @var \Mrss\Model\SubObservation $subObservationModel */
        $subObservationModel = $this->getServiceLocator()->get('model.subObservation');

        // This assumes we've already moved the subscriptions to the new correct year.
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $subscriptions = $subscriptionModel->findByStudyAndYear($this->currentStudy()->getId(), $to);

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();


        $count = 0;
        foreach ($subscriptions as $subscription) {
            $college = $subscription->getCollege();
            $newObservation = $observationModel->findOne($college->getId(), $to);

            if (!$newObservation) {
                $newObservation = new Observation();
                $newObservation->setYear($to);
                $newObservation->setCollege($college);
            }

            $subscription->setObservation($newObservation);

            $oldObservation = $observationModel->findOne($college->getId(), $from);

            foreach ($study->getBenchmarkGroups() as $bGroup) {
                foreach ($bGroup->getBenchmarks() as $benchmark) {
                    $dbColumn = $benchmark->getDbColumn();
                    if ($oldObservation->has($dbColumn)) {
                        $value = $oldObservation->get($dbColumn);

                        if (!is_null($value)) {
                            $newObservation->set($dbColumn, $value);
                            $count++;

                            if ($college->getId() == 101) {
                                //pr($oldObservation->getId() . ' -> ' . $newObservation->getId());
                                //pr($dbColumn . ': ' . $value);

                            }
                        }
                    }
                }
            }

            // Just move any subobservations
            foreach ($oldObservation->getSubObservations() as $subOb) {
                $subOb->setObservation($newObservation);
                $subObservationModel->save($subOb);
            }

            $observationModel->save($newObservation);
            $observationModel->getEntityManager()->flush();
        }



        //prd("$count values copied from $from to $to.");
        $this->flashMessenger()->addSuccessMessage("$count values copied from $from to $to.");

        return $this->redirect()->toUrl('/tools');

    }

    /**
     * Max and NCCBP originally shared some fields on Max forms 5 and 6. Break them apart.
     */
    protected function separateAction()
    {
        $partTwo = false;

        $formIds = array(42);
        $exclude = array(
            'instruction_online',
            'instruction_face_to_face',
            'instruction_hybrid'
        );

        $inputTypes = array();
        $properties = array();
        $fieldsToCopy = array();

        /** @var \Mrss\Model\BenchmarkGroup $benchmarkGroupModel */
        $benchmarkGroupModel = $this->getServiceLocator()->get('model.benchmarkGroup');

        /** @var \Mrss\Model\Benchmark $benchmarkModel */
        $benchmarkModel = $this->getServiceLocator()->get('model.benchmark');

        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        foreach ($formIds as $formId) {
            $benchmarkGroup = $benchmarkGroupModel->find($formId);

            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                // Skip fields already unique to Max
                if (in_array($benchmark->getDbColumn(), $exclude)) {
                    continue;
                }

                // Skip if we've already processed:
                if (stristr($benchmark->getDbColumn(), $this->getSeparationPrefix())) {
                    continue;
                }

                $inputType = $benchmark->getInputType();

                if (!in_array($inputType, $inputTypes)) {
                    $inputTypes[] = $inputType;
                }

                $properties[] = $this->getObservationPropertyCode($benchmark);

                // Part 2
                if ($partTwo) {
                    $oldDbColumn = $benchmark->getDbColumn();
                    $newDbColumn = $this->getSeparationPrefix() . $oldDbColumn;

                    $benchmark->setDbColumn($newDbColumn);
                    $benchmarkModel->save($benchmark);

                    $fieldsToCopy[$oldDbColumn] = $newDbColumn;
                }

                //pr($benchmark->getDbColumn());
            }
        }

        // Copy data to new observation properties
        if ($partTwo) {
            foreach ($subscriptionModel->findByStudyAndYear(2, 2015) as $subscription) {
                $observation = $subscription->getObservation();

                foreach ($fieldsToCopy as $oldDbColumn => $newDbColumn) {
                    $value = $observation->get($oldDbColumn);
                    $observation->set($newDbColumn, $value);

                    pr("Copying $value from $oldDbColumn to $newDbColumn.");
                }

                $observationModel->save($observation);
            }


            $benchmarkModel->getEntityManager()->flush();
        }

        // Part 1
        if (!$partTwo) {
            echo '<pre>' . implode("\n", $properties) . '</pre>';
        }

        pr($inputTypes);

        die('separation script complete');
    }

    protected function getObservationPropertyCode(Benchmark $benchmark)
    {
        $oldDbColumn = $benchmark->getDbColumn();
        $newDbColumn = $this->getSeparationPrefix() . $oldDbColumn;

        $colType = $this->getColumnType($benchmark->getInputType());

        $property = '/** @ORM\Column(type="' . $colType . '", nullable=true) */' . "\n";
        $property .= "protected \${$newDbColumn};\n";

        return $property;
    }

    protected function getSeparationPrefix()
    {
        return 'max_res_';
    }

    protected function getColumnType($inputType)
    {
        $colType = 'float';

        if ($inputType == 'radio') {
            $colType = 'string';
        } elseif ($inputType == 'number') {
            $colType = 'integer';
        }

        return $colType;
    }


    protected function longRunningScript()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(3600);
    }
}
