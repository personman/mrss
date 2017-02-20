<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Mrss\Entity\PeerGroup;
use Mrss\Entity\Report as ReportEntity;
use Mrss\Entity\ReportItem;
use Mrss\Form\AnalyzeEquation;
use Mrss\Form\Email;
use Mrss\Service\Export\Lapsed;
use Mrss\Service\NhebiSubscriptions\Mrss;
use Zend\Mail\Message;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Exceldiff;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;
use PHPExcel_Style_Fill;
use Mrss\Service\NccbpMigration;
use Zend\Session\Container;
use Mrss\Service\Export\User as ExportUser;
use Zend\View\Model\JsonModel;

class ToolController extends AbstractActionController
{
    public function indexAction()
    {

        $baseTime = round(microtime(1) - REQUEST_MICROTIME, 3);
        $loadAverage = sys_getloadavg();
        $loadAverage = array_map('round', $loadAverage, array(2, 2, 2));
        $loadAverage = implode(', ', $loadAverage);

        return array(
            'gc_lifetime' => ini_get('session.gc_maxlifetime'),
            'cookie_lifetime' => ini_get('session.cookie_lifetime'),
            'remember_me_seconds' => ini_get('session.remember_me_seconds'),
            'session_save_path' => session_save_path(),
            'loadAverage' => $loadAverage,
            'baseTime' => $baseTime,
            'collegesWithNoExec' => $this->getMembersWithNoExec()
        );
    }

    public function getMembersWithNoExec()
    {
        $subscriptionModel = $this->getSubscriptionModel();
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
                //die('missing file.');
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
        takeYourTime();

        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $subscriptionModel = $this->getServiceLocator()
            ->get('model.college');

        $saveEvery = 100;


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

            if ($found % $saveEvery == 0) {
                $collegeModel->getEntityManager()->flush();
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

    public function infoAction()
    {
        return array();
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        return $subscriptionModel;
    }

    public function calcCompletionAction()
    {
        $this->longRunningScript();
        //takeYourTime();

        $start = microtime(1);
        $subscriptions = 0;
        $batchSize = 100;
        $currentBatch = $this->params()->fromQuery('batch', 1);
        $lastOfBatch = $batchSize * $currentBatch;
        $firstOfBatch = $lastOfBatch - $batchSize;

        $subscriptionModel = $this->getSubscriptionModel();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $subs = $study->getSubscriptionsForYear();

        $dbColumnsIncluded = $study->getDbColumnsIncludedInCompletion();


        // Loop over all subscriptions
        foreach ($subs as $subscription) {
            /** @var \Mrss\Entity\Subscription $subscription */

            $subscriptions++;

            if ($subscriptions < $firstOfBatch) continue;

            $subscription->updateCompletion($dbColumnsIncluded);
            $subscriptionModel->save($subscription);



            if (false && $subscriptions % $flushEvery == 0) {
                $subscriptionModel->getEntityManager()->flush();
                echo 'flushed ';
            }

            if ($subscriptions == $lastOfBatch) {
                $subscriptionModel->getEntityManager()->flush();
                $nextBatch = $currentBatch + 1;
                $url = '/tools/calc-completion?batch=' . $nextBatch;
                return $this->redirect()->toUrl($url);
                die;
                //break;
            }
            //$this->getSubscriptionModel()->getEntityManager()->detach($subscription);
            //unset($subscription);
        }


        $subscriptionModel->getEntityManager()->flush();


        /*$queryLogger = $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->getSQLLogger();

        prd(count($queryLogger->queries));
        */

        $elapsed = round(microtime(1) - $start, 3);

        //prd($elapsed);

        $memory = round(memory_get_peak_usage() / 1024 / 1024);

        $this->flashMessenger()
            ->addSuccessMessage("$subscriptions processed.");// in $elapsed seconds. Memory used: $memory MB");
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
        $subscriptionModel = $this->getSubscriptionModel();
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

        $this->downloadExcel($excel, $filename);
    }

    public function downloadExcel($excel, $filename)
    {
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
        $subscriptionModel = $this->getSubscriptionModel();
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
     * For populating the yearOffset field in benchmarks that don't have it set up yet
     */
    public function offsetsAction()
    {
        $benchmarks = array();
        $showAll = $this->params()->fromRoute('all');

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                if (!$showAll) {
                    if (!is_null($benchmark->getYearOffset()) && $benchmark->getYearOffset() !== '') {
                        continue;
                    }
                }
                if ($benchmark->getIncludeInNationalReport()) {
                    $benchmarks[$benchmark->getId()] = $benchmark;
                }
            }
        }

        if ($this->getRequest()->isPost()) {
            $benchmarkModel = $this->getServiceLocator()->get('model.benchmark');

            foreach ($this->params()->fromPost('benchmarks') as $id => $yearOffset) {
                if (!empty($benchmarks[$id])) {
                    $benchmark = $benchmarks[$id];

                    if (!is_null($yearOffset) && $yearOffset !== '') {
                        $benchmark->setYearOffset($yearOffset);
                    }
                    $benchmarkModel->save($benchmark);
                }
            }

            $benchmarkModel->getEntityManager()->flush();
            $this->flashMessenger()->addSuccessMessage("Saved.");
            return $this->redirect()->toRoute('tools/offsets');
        }

        return array(
            'benchmarks' => $benchmarks,
            'showAll' => $showAll
        );
    }

    public function zerosAction()
    {
        $this->longRunningScript();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $study->getCurrentYear();
        }

        $subscriptionModel = $this->getSubscriptionModel();
        $subs = $subscriptionModel->findByStudyAndYear($study->getId(), $year);

        // Get all the collected benchmark keys
        $dbColumns = array();
        foreach ($study->getBenchmarkGroups() as $bGroup) {
            foreach ($bGroup->getNonComputedBenchmarksForYear($year) as $benchmark) {
                $dbColumns[] = $benchmark->getDbColumn();
            }
        }

        // Now loop over the subscriptions
        $report = array();
        $users = array();
        foreach ($subs as $subscription) {
            $observation = $subscription->getObservation();

            $zeros = 0;
            foreach ($dbColumns as $dbColumn) {
                $value = $observation->get($dbColumn);

                if ($value === 0) {
                    $zeros++;
                }
            }

            if (!$zeros) {
                continue;
            }

            $emails = array();
            foreach ($subscription->getCollege()->getUsersByStudy($study) as $user) {
                if ($user->getRole() == 'viewer') {
                    continue;
                }

                $emails[] = $user->getEmail();
                $users[] = $user;
            }

            $reportRow = array(
                'college' => $subscription->getCollege()->getName(),
                'emails' => implode(', ', $emails),
                'zeros' => $zeros
            );
            $report[] = $reportRow;
        }

        // Download?
        $format = $this->params()->fromRoute('format', 'html');
        if ($format == 'excel') {
            $exporter = new ExportUser();
            $exporter->export($users);
        }

        // Years for tabs
        $years = $this->getServiceLocator()->get('model.subscription')
            ->getYearsWithSubscriptions($this->currentStudy());
        rsort($years);


        return array(
            'report' => $report,
            'years' => $years,
            'year' => $year
        );
    }

    /**
     * Has a PHP error in the view file. For checking how server handles errors.
     *
     * @return array
     */
    public function failAction()
    {
        return array();
    }

    protected function repairSequences($type = 'data-entry')
    {
        foreach ($this->currentStudy()->getBenchmarkGroups() as $benchmarkGroup) {
            /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */

            $benchmarks = array();
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                $benchmarks[$benchmark->getId()] = $benchmark;
            }

            $headings = array();
            foreach ($benchmarkGroup->getBenchmarkHeadings($type) as $heading) {
                $headings[$heading->getId()] = $heading;
            }

            foreach ($benchmarkGroup->getChildren() as $child) {
                $sequence = $child->getSequence();

                if (get_class($child) == 'Mrss\Entity\BenchmarkHeading') {
                    unset($headings[$child->getId()]);
                    continue;
                }

                unset($benchmarks[$child->getId()]);
            }

            // Now deal with any leftovers (invisible)
            foreach ($headings as $heading) {
                $heading->setSequence(++$sequence);
                $this->getBenchmarHeadingkModel()->save($heading);
            }

            foreach ($benchmarks as $benchmark) {
                $benchmark->setSequence(++$sequence);
                $this->getBenchmarkModel()->save($benchmark);
            }

            $this->getBenchmarkModel()->getEntityManager()->flush();

        }

        $this->getBenchmarkModel()->getEntityManager()->flush();


    }

    public function repairSequencesAction()
    {
        $this->repairSequences();
        $this->flashMessenger()->addSuccessMessage('Sequences repaired.');
        return $this->redirect()->toRoute('tools');
    }

    public function repairReportSequencesAction()
    {

        $this->repairSequences('reports');
        $this->flashMessenger()->addSuccessMessage('Report sequences repaired.');
        return $this->redirect()->toRoute('tools');
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

    public function equationGraphAction()
    {

        $benchmarkGroupId = $this->params()->fromRoute('benchmarkGroup');
        $benchmarkGroupName = null;
        if ($benchmarkGroupId) {
            $groups = array($benchmarkGroupId);
            $benchmarkGroupName = $this->getBenchmarkGroupModel()->find($benchmarkGroupId)->getName();
        }


        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();


        /** @var \Mrss\Service\ComputedFields $computedFields */
        $computedFields = $this->getServiceLocator()->get('computedFields');



        $allBenchmarks = $study->getAllBenchmarks();


        $exclude = array('institution_conversion_factor');

        $dotMarkup = '';
        $benchmarksForVis = array();
        $edgesForVis = array();
        $benchmarkIdsWithEdges = array();
        foreach ($allBenchmarks as $benchmark) {
            //if (!$benchmark->getComputed()) continue;





            $benchmarksForVis[] = array(
                'id' => $benchmark->getId(),
                'label' => $benchmark->getDbColumn(),
                //'label' => $benchmark->getId(),
                'group' => $benchmark->getBenchmarkGroup()->getId()
            );

            if ($benchmark->getComputed() && $equation = $benchmark->getEquation()) {

                $variables = $computedFields->getVariables($equation);

                $dbColumn = $benchmark->getDbColumn();
                foreach ($variables as $variable) {
                    $newLine = "$variable -> $dbColumn<br>\n";

                    $dotMarkup .= $newLine;




                    //$groups = array(5, 6, 7);
                    $groups = array();
                    //$groups = array(4);

                    if ($benchmarkGroupId) {
                        $groups = array($benchmarkGroupId);
                    }

                    if (count($groups) && !in_array($benchmark->getBenchmarkGroup()->getId(), $groups)) {
                        continue;
                    }


                    $fromCol = $benchmark->getDbColumn();
                    $toCol = $variable;

                    $from = $benchmark->getId();
                    $to = $allBenchmarks[$variable]->getId();

                    if (in_array($fromCol, $exclude) || in_array($toCol, $exclude)) {
                        continue;
                    }

                    $benchmarkIdsWithEdges[$from] = true;
                    $benchmarkIdsWithEdges[$to] = true;

                    $edgesForVis[] = array(
                        'from' => $from,
                        'to' => $to,
                        'arrows' => 'from'
                    );
                }
            }
        }

        //pr(count($benchmarksForVis));
        $withEdges = array();
        foreach ($benchmarksForVis as $b) {
            if (!empty($benchmarkIdsWithEdges[$b['id']])) {
                $withEdges[] = $b;
            }
        }

        $benchmarksForVis = $withEdges;

        //pr(count($benchmarksForVis));
        //echo $dotMarkup;

        return array(
            'nodes' => $benchmarksForVis,
            'edges' => $edgesForVis,
            'benchmarkGroups' => $study->getBenchmarkGroups(),
            'benchmarkGroupName' => $benchmarkGroupName
        );
    }

    /**
     * Copy peer groups attached to colleges, changing the attachment to users.
     * report_item->config needs to be updated to point at new peer group.
     * Also handle copying reports and report items?
     */
    public function copyPeerGroupsAction()
    {
        takeYourTime();
        $start = microtime(true);


        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');

        /** @var \Mrss\Model\PeerGroup $peerGroupModel */
        $peerGroupModel = $this->getServiceLocator()->get('model.peer.group');


        $copiedCount = 0;


        $start = microtime(true);


        $colleges = $collegeModel->findAll();

        $flushEvery = 50;
        $i = 0;

        foreach ($colleges as $college) {
            foreach ($college->getPeerGroups() as $peerGroup) {
                $peerGroupMap[$peerGroup->getId()] = array();

                foreach ($college->getUsers() as $user) {
                    $newGroup = new PeerGroup();

                    $newGroup->setUser($user);
                    $newGroup->setYear($peerGroup->getYear());
                    $newGroup->setName($peerGroup->getName());
                    $newGroup->setStudy($peerGroup->getStudy());
                    $newGroup->setPeers($peerGroup->getPeers());
                    $newGroup->setBenchmarks($peerGroup->getBenchmarks());

                    $peerGroupModel->save($newGroup);

                    // Remember ids for newly created groups and their
                    //$peerGroupMap[$peerGroup->getId()][$user->getId()] = $newGroup->getId();


                    $copiedCount++;

                }
                //pr($peerGroup->getName());
            }


            $i++;


            if ($i % $flushEvery == 0) {
                $peerGroupModel->getEntityManager()->flush();
            }

            if ($i == 100) {
                //$elapsed = microtime(true) - $start;
                //prd($elapsed);
            }
        }

        $peerGroupModel->getEntityManager()->flush();


        pr($copiedCount);

        $elapsed = microtime(true) - $start;
        pr($elapsed);








        // Now reports
        $copiedReportCount = 0;
        $flushEvery = 100;

        /** @var \Mrss\Model\Report $reportModel */
        $reportModel = $this->getServiceLocator()->get('model.report');

        /** @var \Mrss\Model\ReportItem $reportItemModel */
        $reportItemModel = $this->getServiceLocator()->get('model.report.item');

        $reports = $reportModel->findAll();

        $i = 0;
        foreach ($reports as $report) {
            $college = $report->getCollege();

            if ($college) {
                foreach ($college->getUsers() as $user) {
                    $newReport = new ReportEntity();

                    $newReport->setUser($user);
                    $newReport->setStudy($report->getStudy());
                    $newReport->setName($report->getName());
                    $newReport->setDescription($report->getDescription());

                    $reportModel->save($newReport);
                    $copiedReportCount++;

                    // Flush so that $newReport->getId() works
                    //$reportModel->getEntityManager()->flush();

                    // Report items
                    foreach ($report->getItems() as $item) {
                        $newItem = new ReportItem();

                        $newItem->setReport($newReport);
                        $newItem->setHighlightedCollege($item->getHighlightedCollege());
                        $newItem->setBenchmark1($item->getBenchmark1());
                        $newItem->setBenchmark2($item->getBenchmark2());
                        $newItem->setBenchmark3($item->getBenchmark3());
                        $newItem->setName($item->getName());
                        $newItem->setSubtitle($item->getSubtitle());
                        $newItem->setDescription($item->getDescription());
                        $newItem->setType($item->getType());
                        $newItem->setYear($item->getYear());
                        $newItem->setSequence($item->getSequence());

                        $config = $item->getConfig();
                        if ($oldGroupId = $config['peerGroup']) {
                            //if ($newGroupId = $peerGroupMap[$oldGroupId][$user->getId()]) {
                            if ($newGroupId = $this->getNewPeerGroupId($oldGroupId, $user)) {
                                $config['peerGroup'] = $newGroupId;
                            }
                        }

                        $newItem->setConfig($config);

                        $reportItemModel->save($newItem);
                        //$reportItemModel->getEntityManager()->flush();

                    }
                }
            }

            $i++;


            if ($i % $flushEvery == 0) {
                $reportModel->getEntityManager()->flush();
            }

        }

        $reportModel->getEntityManager()->flush();

        //pr($copiedCount);
        pr($copiedReportCount);

        $elapsed = microtime(true) - $start;
        prd($elapsed);

        die('test');
    }

    /**
     * @return array
     */
    public function analyzeEquationAction()
    {
        $message = $result = null;

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $benchmarks = $study->getStructuredBenchmarks(false, 'dbColumn', null, true);
        $colleges = $this->getAllColleges();
        $years = $this->getServiceLocator()->get('model.subscription')->getYearsWithSubscriptions($study);

        $form = new AnalyzeEquation($benchmarks, $colleges, $years);

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                $year = $data['year'];
                $dbColumn = $data['benchmark'];
                $collegeId = $data['college'];

                $observation = $this->getObservationModel()->findOne($collegeId, $year);

                if ($observation) {
                    $observationId = $observation->getId();

                    $url = "http://fcs.dan.com/reports/compute-one/$observationId/1/$dbColumn";
                    //$result = $this->getUrlContents($url);

                    $params = array(
                        'action' => 'computeOne',
                        'debug' => 1,
                        'observation' => $observationId,
                        'benchmark' => $dbColumn
                    );

                    ob_start();
                    $devNull = $this->forward()->dispatch('reports', $params);
                    $result = ob_get_clean();
                } else {
                    $message = "That institution did not submit data in $year.";
                }

            }
        }

        return array(
            'form' => $form,
            'message' => $message,
            'result' => $result
        );
    }

    protected function getUrlContents($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * @return \Mrss\Model\Observation
     */
    protected function getObservationModel()
    {
        return $this->getServiceLocator()->get('model.observation');
    }

    protected function getAllColleges()
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');

        $colleges = array();
        foreach ($collegeModel->findAll() as $college) {
            $colleges[$college->getId()] = $college->getNameAndState();
        }

        return $colleges;
    }

    public function suppressionsAction()
    {
        $subscriptions = $this->getSubscriptions();

        $subsWithSuppressions = array();
        foreach ($subscriptions as $subscription) {
            if ($suppressions = $subscription->getSuppressionList()) {

                $subsWithSuppressions[] = array(
                    'college' => $subscription->getCollege()->getNameAndState(),
                    'suppressions' => $suppressions
                );
            }

        }

        return array(
            'subsWithSuppressions' => $subsWithSuppressions
        );
    }

    public function downloadSuppressionsAction()
    {
        $subscriptions = $this->getSubscriptions();

        $usersWithSuppressions = array();
        foreach ($subscriptions as $subscription) {
            if ($suppressions = $subscription->getSuppressionList()) {

                foreach ($subscription->getCollege()->getDataUsers() as $user) {
                    $usersWithSuppressions[] = array(
                        'email' => $user->getEmail(),
                        'prefix' => $user->getPrefix(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'title' => $user->getTitle(),
                        'college' => $subscription->getCollege()->getNameAndState(),
                        'suppressions' => $suppressions,
                    );
                }
            }
        }

        $headers = array(
            'Email',
            'Prefix',
            'First Name',
            'Last Name',
            'Title',
            'Institution',
            'Suppressed Forms'
        );

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        $sheet->fromArray($headers, null, 'A' . $row);

        $row++;
        $sheet->fromArray($usersWithSuppressions, null, 'A' . $row);

        foreach (range(0, count($headers) - 1) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $this->downloadExcel($excel, 'users-with-suppressed-forms.xlsx');
    }

    public function auditAction()
    {
        // Years for tabs
        $years = $this->getServiceLocator()->get('model.subscription')
            ->getYearsWithSubscriptions($this->currentStudy());
        rsort($years);


        $study = $this->currentStudy();
        $year = $this->getYearFromRouteOrStudy();

        $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear($study->getId(), $year);

        $totalCount = count($subscriptions);
        $paidCount = 0;
        $totalAmount = 0;
        $paidAmount = 0;
        foreach ($subscriptions as $subscription) {
            $totalAmount += $subscription->getPaymentAmount();

            if ($subscription->getPaid()) {
                $paidCount++;
                $paidAmount += $subscription->getPaymentAmount();
            }
        }

        return array(
            'years' => $years,
            'year' => $year,
            'subscriptions' => $subscriptions,
            'totalCount' => $totalCount,
            'paidCount' => $paidCount,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount
        );
    }

    public function auditUpdateAction()
    {
        $subscriptionId = $this->params()->fromPost('subscriptionId');

        if ($subscription = $this->getSubscriptionModel()->find($subscriptionId)) {
            $paid = $this->params()->fromPost('paid');
            $note = $this->params()->fromPost('note');

            if ($paid) {
                $paid = true;
            } else {
                $paid = false;
            }

            $subscription->setPaid($paid);
            $subscription->setPaidNotes($note);

            $this->getSubscriptionModel()->save($subscription);
            $this->getSubscriptionModel()->getEntityManager()->flush();

            $responseText = 'ok';
        } else {
            $responseText = 'Membership not found for id ' . $subscriptionId;
        }


        $response = $this->getResponse()->setContent($responseText);
        return $response;

    }

    public function emailTestAction()
    {
        $form = new Email();

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                // Build the email
                $studyConfig = $this->getServiceLocator()->get('study');
                $from_email = $studyConfig->from_email;

                $message = new Message();
                $message->setSubject($data['subject']);
                $message->setFrom($from_email);
                $message->addTo($data['to']);
                $message->setBody($data['body']);


                // Send the email
                $mailer = $this->getServiceLocator()->get('mail.transport');
                $result = $mailer->send($message);


                //if ($result) {
                    $this->flashMessenger()->addSuccessMessage("Message sent.");
                /*} else {
                    $this->flashMessenger()->addErrorMessage("Problem sending message.");
                }*/

                return $this->redirect()->toUrl('/tools/email-test');
            }
        }

        return array(
            'form' => $form
        );
    }

    public function mergeMCCAction()
    {
        //$mergeService = new \Mrss\Service\MergeData();

        /** @var \Mrss\Service\MergeData $mergeService */
        $mergeService = $this->getServiceLocator()->get('service.merge.data');

        $mergeService->setYear(2009);

        $from = array(
            713, // 440305,
            579, // 178129,
            705, // 178022
        );

        $to = 137; // 177995;

        $mergeService->merge($from, $to);

        //pr($mergeService);
        die('test');
    }

    public function getYearFromRouteOrStudy($college = null)
    {
        if (empty($college)) {
            $college = $this->currentCollege();
        }

        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // But if reports aren't open yet, show them last year's by default
            $impersonationService = $this->getServiceLocator()
                ->get('zfcuserimpersonate_user_service');
            $isJCCC = (!empty($college) && ($college->getId() == 101) || $impersonationService->isImpersonated());
            $isMax = $this->currentStudy()->getId() == 2;

            // Allow access to Max reports for user feedback
            if (!$isMax && !$isJCCC && !$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }

            // New
            $subModel = $this->getSubscriptionModel();

            $before = null;
            if (!$this->currentStudy()->getReportsOpen()) {
                $before = $this->currentStudy()->getCurrentYear();
            }

            $latestSubscription = $subModel->getLatestSubscription($this->currentStudy(), $college->getId(), $before);

            if (!empty($latestSubscription)) {
                $year = $latestSubscription->getYear();
            }
        }

        return $year;
    }

    public function getSubscriptions()
    {
        $subscriptionModel = $this->getSubscriptionModel();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $subscriptions = $subscriptionModel->findByStudyAndYear($study->getId(), $study->getCurrentYear());

        return $subscriptions;
    }

    protected function getNewPeerGroupId($oldGroupId, $user)
    {
        /** @var \Mrss\Model\PeerGroup $model */
        $model = $this->getServiceLocator()->get('model.peer.group');

        $id = null;


        if ($oldGroup = $model->find($oldGroupId)) {

            if ($newGroup = $model->findOneByUserAndName($user, $oldGroup->getName())) {
                $id = $newGroup->getId();
            }
        }

        return $id;
    }

    public function lapsedAction()
    {

        $lapsedService = new Lapsed;
        $lapsedService->setStudy($this->currentStudy());
        $lapsedService->setSubscriptionModel($this->getServiceLocator()->get('model.subscription'));
        $lapsedService->export();

        die('hello there');
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
        takeYourTime();

        // Turn off query logging
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        if (empty($this->benchmarkModel)) {
            $this->benchmarkModel = $this->getServiceLocator()
                ->get('model.benchmark');
        }

        return $this->benchmarkModel;
    }


    /**
     * @return \Mrss\Model\BenchmarkHeading
     */
    public function getBenchmarHeadingkModel()
    {
        return $this->getServiceLocator()->get('model.benchmark.heading');
    }

    /**
     * @return \Mrss\Model\BenchmarkGroup
     */
    protected function getBenchmarkGroupModel()
    {
        return $this->getServiceLocator()->get('model.benchmark.group');
    }

    public function observationDataMigrationAction()
    {
        takeYourTime();
        /** @var \Mrss\Service\ObservationDataMigration $migrator */
        $migrator = $this->getServiceLocator()->get('service.observation.data.migration');
        //$migrator->copySubscription($this->getCurrentSubscription());
        //$count = $migrator->copyAllSubscriptions();

        /** @var \Mrss\Model\Observation $obModel */
        $obModel = $this->getServiceLocator()->get('model.observation');
        $ob = $obModel->findOneUnMigrated();

        $start = microtime(true);

        if ($ob && $migrator->copyObservation($ob)) {
            $ob->setMigrated(true);
            $obModel->save($ob);
            $obModel->getEntityManager()->flush();

            echo 'Success';
        } else {
            //echo 'Error';
        }

        if ($ob) {
            pr($ob->getId());
            pr($ob->getCollege()->getNameAndState());
            pr($ob->getYear());

        }

        $elapsed = microtime(true) - $start;

        pr(round($elapsed, 3));

        if (!empty($ob)) {
            echo '<script>location.reload()</script>';
        } else {
            echo 'All done.';
        }

        die(' ok');
    }

    public function getCurrentSubscription()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $study = $this->currentStudy();
        $college = $this->currentCollege();

        return $subscriptionModel->findCurrentSubscription($study, $college->getId());
    }

    /**
     * During merging of NCCBP and Workforce to modules/sections of the same study, set old memberships up
     */
    public function populateSectionsAction()
    {
        $defaultSectionId = 1;


        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $defaultSection = $study->getSection($defaultSectionId);

        $updates = 0;
        foreach ($this->getSubscriptionModel()->findAll() as $subscription) {
            $currentSectionIds = $subscription->getSectionIds();
            if (count($currentSectionIds) == 0) {
                $subscription->setSections(array($defaultSection));

                $this->getSubscriptionModel()->save($subscription);
                $updates++;
            }
        }

        $this->getSubscriptionModel()->getEntityManager()->flush();

        die("$updates subscriptions updated");
    }

    public function importWfAction()
    {
        $this->longRunningScript();
        $year = $this->params()->fromQuery('year', 2017);

        $importer = $this->getServiceLocator()->get('service.import.workforce.data');

        $importer->import($year);

        die('test');
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getWfDb()
    {
        return $this->getServiceLocator()->get('workforce-db');
    }
}
