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
use Zend\Mail\Message;
use Mrss\Form\Exceldiff;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Row;
use PHPExcel_Style_Fill;
use Mrss\Service\NccbpMigration;
use Zend\Session\Container;
use Mrss\Service\Export\User as ExportUser;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;

class ToolController extends BaseController
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

        $subscriptions = 0;
        $batchSize = 100;
        $currentBatch = $this->params()->fromQuery('batch', 1);
        $lastOfBatch = $batchSize * $currentBatch;
        $firstOfBatch = $lastOfBatch - $batchSize;

        $subscriptionModel = $this->getSubscriptionModel();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        //$subs = $study->getSubscriptionsForYear(); // Current year only, faster
        $subs = $study->getSubscriptions(); // All years, slower

        $dbColumnsIncluded = $study->getDbColumnsIncludedInCompletion();


        // Loop over all subscriptions
        foreach ($subs as $subscription) {
            /** @var \Mrss\Entity\Subscription $subscription */

            $subscriptions++;

            if ($subscriptions < $firstOfBatch) {
                continue;
            }

            $subscription->updateCompletion($dbColumnsIncluded);
            $subscriptionModel->save($subscription);



            if (false) { // && $subscriptions % $flushEvery == 0) {
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

        //$elapsed = round(microtime(1) - $start, 3);

        //prd($elapsed);

        //$memory = round(memory_get_peak_usage() / 1024 / 1024);

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
        $this->longRunningScript();
        $service = $this->getServiceLocator()->get('copyData');

        $benchmarks = array(
            1144 => 203,
            1145 => 197,
            1146 => 820,
            1147 => 821,
            1148 => 822,
            1149 => 202,
            1155 => 200,
            1156 => 201,
        );

        $year = $this->params()->fromQuery('year', 2014);
        $years = array($year);

        pr($year);

        $service->copy($benchmarks, $years);

        die('ok');
    }
    
    /**
     * @return \Zend\Http\Response
     * @deprecated
     */
    public function copyDataActionDeprecated()
    {
        // Copy one year's data for the study to another. dangerous
        $from = $this->params()->fromRoute('from');
        $copyTo = $this->params()->fromRoute('to');

        // This assumes we've already moved the subscriptions to the new correct year.
        $subscriptionModel = $this->getSubscriptionModel();
        $subscriptions = $subscriptionModel->findByStudyAndYear($this->currentStudy()->getId(), $copyTo);

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();


        $count = 0;
        foreach ($subscriptions as $subscription) {
            $college = $subscription->getCollege();
            $newObservation = $this->getObservationModel()->findOne($college->getId(), $copyTo);

            if (!$newObservation) {
                $newObservation = new Observation();
                $newObservation->setYear($copyTo);
                $newObservation->setCollege($college);
            }

            $subscription->setObservation($newObservation);

            $oldObservation = $this->getObservationModel()->findOne($college->getId(), $from);

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
                $this->getObservationModel()->save($subOb);
            }

            $this->getObservationModel()->save($newObservation);
            $this->getObservationModel()->getEntityManager()->flush();
        }



        //prd("$count values copied from $from to $to.");
        $this->flashMessenger()->addSuccessMessage("$count values copied from $from to $copyTo.");

        return $this->redirect()->toUrl('/tools');
    }

    /**
     * @param $showAll
     * @return \Mrss\Entity\Benchmark[]
     */
    protected function getBenchmarksWithoutOffsets($showAll)
    {
        $benchmarks = array();

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

        return $benchmarks;
    }

    /**
     * For populating the yearOffset field in benchmarks that don't have it set up yet
     */
    public function offsetsAction()
    {

        $showAll = $this->params()->fromRoute('all');
        $benchmarks = $this->getBenchmarksWithoutOffsets($showAll);

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

    /**
     * @return \Mrss\Model\Datum
     */
    protected function getDatumModel()
    {
        return $this->getServiceLocator()->get('model.datum');
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

        $subsWithZeros = $this->getDatumModel()->findZeros($year);

        $report = array();
        $users = array();
        foreach ($subsWithZeros as $info) {
            $collegeId = $info['college_id'];
            $college = $this->getCollegeModel()->find($collegeId);

            $emails = array();
            foreach ($college->getUsersByStudy($study) as $user) {
                if ($user->getRole() == 'viewer') {
                    continue;
                }

                $emails[] = $user->getEmail();
                $users[] = $user;
            }

            $report[] = array(
                'college' => $college->getNameAndState(),
                'emails' => implode(', ', $emails),
                'zeros' => $info['count']
            );
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

            $sequence = 1;

            $benchmarks = array();
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                $benchmarks[$benchmark->getId()] = $benchmark;
            }

            $headings = array();
            foreach ($benchmarkGroup->getBenchmarkHeadings($type) as $heading) {
                $headings[$heading->getId()] = $heading;
            }

            foreach ($benchmarkGroup->getChildren() as $child) {
                /** @var \Mrss\Entity\Benchmark $child */
                $sequence = $child->getSequence();

                if (get_class($child) == 'Mrss\Entity\BenchmarkHeading') {
                    unset($headings[$child->getId()]);
                    continue;
                }

                unset($benchmarks[$child->getId()]);
            }

            // Now deal with any leftovers (invisible)
            foreach ($headings as $heading) {
                /** @var \Mrss\Entity\BenchmarkHeading $heading */
                $heading->setSequence(++$sequence);

                $this->getBenchmarkHeadingModel()->save($heading);
            }

            /** @var \Mrss\Entity\Benchmark $benchmark */
            foreach ($benchmarks as $benchmark) {
                if ($type == 'reports') {
                    $benchmark->setReportSequence(++$sequence);
                } else {
                    $benchmark->setSequence(++$sequence);
                }

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
            //$groups = array($benchmarkGroupId);
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

                    $groups = array();

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
     * @deprecated
     */
    public function copyPeerGroupsAction()
    {
        takeYourTime();

        /** @var \Mrss\Model\PeerGroup $peerGroupModel */
        $peerGroupModel = $this->getServiceLocator()->get('model.peer.group');

        $copiedCount = 0;

        $start = microtime(true);
        $colleges = $this->getCollegeModel()->findAll();

        $flushEvery = 50;
        $iteration = 0;

        foreach ($colleges as $college) {
            foreach ($college->getPeerGroups() as $peerGroup) {
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
            }


            $iteration++;


            if ($iteration % $flushEvery == 0) {
                $peerGroupModel->getEntityManager()->flush();
            }
        }

        $peerGroupModel->getEntityManager()->flush();






        // Now reports
        $copiedReportCount = 0;
        $flushEvery = 100;

        /** @var \Mrss\Model\Report $reportModel */
        $reportModel = $this->getServiceLocator()->get('model.report');

        /** @var \Mrss\Model\ReportItem $reportItemModel */
        $reportItemModel = $this->getServiceLocator()->get('model.report.item');

        $reports = $reportModel->findAll();

        $iteration = 0;
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

            $iteration++;


            if ($iteration % $flushEvery == 0) {
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

                    //$url = "http://fcs.dan.com/reports/compute-one/$observationId/1/$dbColumn";
                    //$result = $this->getUrlContents($url);

                    $params = array(
                        'action' => 'computeOne',
                        'debug' => 1,
                        'observation' => $observationId,
                        'benchmark' => $dbColumn
                    );

                    ob_start();
                    $devNull = $this->forward()->dispatch('reports', $params);
                    unset($devNull);
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

    protected function getUrlContents($url)
    {
        $curlHandle = curl_init();
        $timeout = 5;
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($curlHandle);
        curl_close($curlHandle);

        return $data;
    }

    protected function getAllColleges()
    {
        $colleges = array();
        foreach ($this->getCollegeModel()->findAll() as $college) {
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
                $fromEmail = $studyConfig->from_email;

                $message = new Message();
                $message->setSubject($data['subject']);
                $message->setFrom($fromEmail);
                $message->addTo($data['to']);

                $content = $data['body'];

                //prd($content);

                //$content = $this->getTestHtml();
                //prd($content);

                // make a header as html
                $html = new MimePart($content);
                $html->type = "text/html";
                $text = new MimePart(strip_tags($content));
                $text->type = "text/plain";
                $body = new MimeMessage();
                $body->setParts(array($text, $html));

                $message->setBody($body);

                //$message->setBody($data['body']);


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

    protected function getTestHtml()
    {
        return '<div class=3D"WordSection1"><p class=3D"MsoNormal"><span sty=
le=3D"mso-fareast-language:EN-US">Is it possible to have an email like this=
 for the initial login? Minus the footer, don=E2=80=99t need the unsubscrib=
e and address stuff.</span></p><p class=3D"MsoNormal"><span style=3D"mso-fa=
reast-language:EN-US">=C2=A0</span></p><div><p class=3D"MsoNormal"><b><span=
 lang=3D"EN-US" style=3D"color:#006699">Mike Daniel</span></b></p><p class=
=3D"MsoNormal"><span lang=3D"EN-US">Customer Success Manager</span></p><p c=
lass=3D"MsoNormal"><span lang=3D"EN-US">=E2=80=8B<a href=3D"mailto:mdaniel@=
envisio.com"><span style=3D"color:#0563c1">mdaniel@envisio.com</span></a> |=
 <a href=3D"https://mdaniel.youcanbook.me/"><span style=3D"color:#0563c1">o=
nline calendar</span></a></span></p><p class=3D"MsoNormal"><span lang=3D"EN=
-US">Direct: (604) 256-7055 |Main: (604) 670-0710 </span></p></div><p class=
=3D"MsoNormal"><span style=3D"mso-fareast-language:EN-US">=C2=A0</span></p>=
<div><div style=3D"border:none;border-top:solid #e1e1e1 1.0pt;padding:3.0pt=
 0cm 0cm 0cm"><p class=3D"MsoNormal"><b><span lang=3D"EN-US">From:</span></=
b><span lang=3D"EN-US"> <a href=3D"mailto:noreply@hubspot.com">noreply@hubs=
pot.com</a> [mailto:<a href=3D"mailto:noreply@hubspot.com">noreply@hubspot.=
com</a>] <br><b>Sent:</b> November 15, 2017 4:10 PM<br><b>To:</b> <a href=
=3D"mailto:mdaniel@envisio.com">mdaniel@envisio.com</a><br><b>Subject:</b> =
Preview - Welcome to govBenchmark</span></p></div></div><p class=3D"MsoNorm=
al">=C2=A0</p><div id=3D"preview_text"><p class=3D"MsoNormal" style=3D"mso-=
line-height-alt:.75pt"><span style=3D"font-size:1.0pt;font-family:&quot;Ari=
al&quot;,sans-serif;color:#eeeeee">Welcome to Envisio govBenchmark! You are=
 now part of a community of 100+ local government members across North Amer=
ica. </span></p></div><div align=3D"center"><table class=3D"MsoNormalTable"=
 border=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"100%" style=3D"w=
idth:100.0%;background:#eeeeee;border-collapse:collapse" id=3D"backgroundTa=
ble"><tr><td width=3D"100%" valign=3D"top" style=3D"width:100.0%;padding:0c=
m 0cm 0cm 0cm" id=3D"bodyCell"><div align=3D"center"><table class=3D"MsoNor=
malTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"600" st=
yle=3D"width:450.0pt;background:white;border-collapse:collapse" id=3D"templ=
ateTable"><tr><td valign=3D"top" style=3D"padding:0cm 0cm 0cm 0cm"><table c=
lass=3D"MsoNormalTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"0" al=
ign=3D"right" width=3D"100%" style=3D"width:100.0%;background:#eeeeee;borde=
r-collapse:collapse" id=3D"headerTable"><tr><td width=3D"100%" valign=3D"to=
p" style=3D"width:100.0%;padding:0cm 22.5pt 0cm 0cm"><table class=3D"MsoNor=
malTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"100%" s=
tyle=3D"width:100.0%;border-collapse:collapse"><tr><td width=3D"100%" valig=
n=3D"top" style=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"></td></tr></table>=
</td></tr></table></td></tr><tr><td valign=3D"top" style=3D"background:#eee=
eee;padding:7.5pt 15.0pt 7.5pt 15.0pt" id=3D"contentCell"><div align=3D"cen=
ter"><table class=3D"MsoNormalTable" border=3D"1" cellspacing=3D"0" cellpad=
ding=3D"0" width=3D"100%" style=3D"width:100.0%;background:white;border-col=
lapse:collapse;border:none" id=3D"contentTableOuter"><tr><td valign=3D"top"=
 style=3D"border:solid #c8c8c8 1.0pt;border-bottom:solid #a8a8a8 1.0pt;padd=
ing:22.5pt 22.5pt 22.5pt 22.5pt"><div align=3D"center"><table class=3D"MsoN=
ormalTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"600" =
style=3D"width:450.0pt;border-collapse:collapse" id=3D"contentTableInner"><=
tr><td width=3D"100%" valign=3D"top" style=3D"width:100.0%;padding:0cm 0cm =
0cm 0cm"><table class=3D"MsoNormalTable" border=3D"0" cellspacing=3D"0" cel=
lpadding=3D"0" width=3D"100%" style=3D"width:100.0%;border-collapse:collaps=
e"><tr><td width=3D"50%" valign=3D"top" style=3D"width:50.0%;padding:0cm 0c=
m 0cm 0cm"><div><div><div id=3D"hs_cos_wrapper_module_1508213813756379"><p =
class=3D"MsoNormal" style=3D"line-height:18.0pt"><span style=3D"font-size:1=
1.5pt;font-family:&quot;Arial&quot;,sans-serif;color:dimgray"><a href=3D"ht=
tp://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W790d7X8GQRvYW1c9gN=
H1CkXVc0/5/f18dQhb0SmhY8YXMd0W9kpyTt6ghkRDVWs4R057z_B8W5r8vy28yym7NW5yMB6q5=
sNtXpW8rDxGf8ttb3HW8mp2bw8p-Vr0W65kbk08sZ817W8q5dtq1p84vVW1QfXSP8sZ4WSW67b7=
w67hYCG4W3N1Lh352SLKTW64jzh47-JXPKW5B1dDk3bPyFLW3Cblxq5RlCDgW3LZN2x8vr5SsW6=
2VJXc5zDWR8W34XQMQ15Y9PHW8Nndpx8BVCbLN3zx6ZVXsBbdW5xC6Bh5FFqGPW5G3SWf5rQn5N=
W34mThF8Fv1tRW5B6yPg5rQp8FW3-Y6738gGb78W5pRvJx8wYJbRW3z9vLM5qG83bW8fP0Yp3fK=
M4ZW228cJg53NYtcW4PLwTV7pGBJqW20QxzG1V2fkrN90TMkwd8y6qW1QNZ5p2hQzPSW3HSn_B5=
6fvxmW3tY4Jv6-J5ptW2B9Fq75vy_NkW6lRJl13xWwfqS52H5nYGjJ102" target=3D"_blank=
"><span style=3D"border:none windowtext 1.0pt;padding:0cm;text-decoration:n=
one"><img border=3D"0" width=3D"299" style=3D"width:3.1111in" id=3D"_x0000_=
i1025" src=3D"https://cdn2.hubspot.net/hub/364927/hubfs/Images-New/envisio-=
footer-logo.png?t=3D1510787748267&amp;width=3D299&amp;name=3Denvisio-footer=
-logo.png" alt=3D"Envisio logo"></span></a></span></p></div></div></div></t=
d><td width=3D"50%" valign=3D"top" style=3D"width:50.0%;padding:0cm 0cm 0cm=
 0cm"><div><div><div id=3D"hs_cos_wrapper_module_1508213837506380"><p class=
=3D"MsoNormal" style=3D"line-height:18.0pt"><span style=3D"font-size:11.5pt=
;font-family:&quot;Arial&quot;,sans-serif;color:dimgray"><a href=3D"http://=
www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W1X96YR162CkpW1W--mj49wZ=
-S0/5/f18dQhb0SmhX8YXMz-W9kpyTt6ghkRDVWs4R057skKTW5r8vy28yym7NW5yMB6q5sNtXp=
W8rDxGf8tBV55W5xd97M1mQsyjVRbqhJ5wL96LW1sL3M03MybvyV4LnFC62x5yTW4P1qTD2yJF1=
WVYT2jk6P4lCXW6hF8n169NG1wW6Pr3nq17sQVDW8sYvvl5tGlT_W7cvxRc7wT8GgW4bQW_q6c4=
4xSW2VtC7R4gbfRyW1LSgH_8Tq7SJW7Q94_h58BlvHW8XxxvZ7q6XQ5W2GFLZS1zspn5Vtf1kV1=
M2vVQW1_sJZ41Wc5CpW2XrF111L8z1kW4bPV8s7n7TX_W6w6h3W5byFMzW1QfFpZ6dhWtwW6rCv=
9w5tb4RZW3HR_B28csLSqW10360J5K_--sW5y6rdB1c47FSV10Nbd5x6QYgW4wXxPP2bNMKgW3j=
6wXD7vSDgHW3-RcKk25LjlTW2dYCLn7G27xRW3wPByY3wqspzf9dhsyL11" target=3D"_blan=
k"><span style=3D"border:none windowtext 1.0pt;padding:0cm;text-decoration:=
none"><img border=3D"0" width=3D"270" style=3D"width:2.8125in" id=3D"_x0000=
_i1026" src=3D"https://cdn2.hubspot.net/hub/364927/hubfs/Envisio%20Logo/gov=
benchmark-logo-lg.png?t=3D1510787748267&amp;width=3D270&amp;name=3Dgovbench=
mark-logo-lg.png" alt=3D"govBenchmark"></span></a></span></p></div></div></=
div></td></tr></table></td></tr><tr><td width=3D"100%" valign=3D"top" style=
=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"><table class=3D"MsoNormalTable" b=
order=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"100%" style=3D"wid=
th:100.0%;border-collapse:collapse"><tr><td width=3D"100%" valign=3D"top" s=
tyle=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"><div><div id=3D"hs_cos_wrappe=
r_hs_email_body"><p style=3D"margin-bottom:12.0pt;line-height:18.0pt"><span=
 style=3D"font-size:11.5pt;font-family:&quot;Arial&quot;,sans-serif;color:d=
imgray">Strategy Enthusiast,</span></p><p style=3D"margin-bottom:12.0pt;lin=
e-height:18.0pt"><span style=3D"font-size:11.5pt;font-family:&quot;Arial&qu=
ot;,sans-serif;color:dimgray">We=E2=80=99re very excited to welcome you to =
the govBenchmark family! By signing up, you are joining a community of memb=
ers across North America looking to improve their government=E2=80=99s perf=
ormance through the use of advanced benchmarking software.</span></p><p sty=
le=3D"margin-bottom:12.0pt;line-height:18.0pt"><span style=3D"font-size:11.=
5pt;font-family:&quot;Arial&quot;,sans-serif;color:dimgray">Your username t=
o log into govBenchmark will be your email address. To sign in, please firs=
t click on the below link to set up your password.</span></p><p class=3D"Ms=
oNormal" align=3D"center" style=3D"text-align:center;line-height:18.0pt"><s=
pan class=3D"hs-cta-node"><span style=3D"font-size:11.5pt;font-family:&quot=
;Arial&quot;,sans-serif;color:dimgray"><a href=3D"http://www.envisio.com/e1=
t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W82mTjg7xh9kTW8jTsd977fV6b0/5/f18dQhb0S1Wd=
1QvpH4V11Qk1199Yg0W1wJjCy2skFs5N86CMX6nlD-9W5ZSJCB4b8dZFVmjWR37sm4WJW8mLGbQ=
6FtnQnMxD-jchWtc5W5rsmMz5J4P2HW5JxrVP1yHqfnW2jfjNX9k29NVW2JTVVp5tXTqmW98sKc=
J3Y-HhQW2wLncY5F8bM9W5T1NWp4JLRncW51npvz7mvNtLW7b-Z9C5GhmtMW52VwX22QyfHJVjb=
PGd6s_Z6xW79kd2z6GfJYsW6zcDq01Rpx1PVxTR9m7bgvTCW1FmgfF3XfgCtW5j8H_G38ZhjJN2=
qmfbXQzwnqW2Yqd0h9gx8ZLW6K4Pxz7VRqF9W12DtWM1j8lbpW6NDcDH3djYmGW4C1Jsq36sCNR=
VDG_8q8Z9H0FW1hrGlx3q-nZfW52HJKX5xBYBgW3VmR-x42TTJTW4bWVB13DKS94W5czfKM1gzs=
BtW7DnnTL6sptfSW2kmr3Q5C_FgmW31yBfk8jD2c6W31sWwz7s3q6XW1jWzzx7TJVMHW18T8d78=
CDW6ZW7bchqY1NV2t2W65-bbY54rmLvW41ThKS5kw9D7N8cJ9q5RssWlN6BJfY1cs4PwW7SF9Vf=
7qV2YlW4FzQvp6-lfywW6f4Hn55BTyprW8yvbd73trVwmW9gwKlc41TwR0N6SXdq1fVl8sf3lW-=
rq03" target=3D"_blank"><span style=3D"text-decoration:none"><img border=3D=
"0" id=3D"hs-cta-img-871f163e-d87b-46d4-93f9-4cc558f2636e" src=3D"https://c=
ta-image-cms2.hubspot.com/ctas/v2/public/cs/il/?pg=3D871f163e-d87b-46d4-93f=
9-4cc558f2636e&amp;pid=3D364927&amp;ecid=3D&amp;hseid=3D2&amp;hsic=3Dtrue" =
alt=3D"Set up my password"></span></a></span></span><span style=3D"font-siz=
e:11.5pt;font-family:&quot;Arial&quot;,sans-serif;color:dimgray"></span></p=
><p style=3D"margin-bottom:12.0pt;line-height:18.0pt"><span style=3D"font-s=
ize:11.5pt;font-family:&quot;Arial&quot;,sans-serif;color:dimgray">Once you=
 have set up your password, don=E2=80=99t forget to also bookmark the </spa=
n><span style=3D"font-size:11.5pt;font-family:&quot;Arial&quot;,sans-serif;=
color:#007fa5"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF=
96Hybr0/*N532_8flSWkPW14rjFN6hYXkH0/5/f18dQhb0S5fx8YXNsnW9kpyTt6ghkRDVWs4R0=
57Y-gFW5r8vwP3mm42mVQzKZc63kYwJW8l8qPk8CZrTyW3Jswq68B-9dcN8zTLsQScn16W66jvd=
95y5jh-W1rfXlz1kRpb7W7Zs1mD25McgbW1SZkdK3fNkD_VQJJn63Lqnm_W8q5FTl6skKw2W64Q=
Zb68rvwMTW4DPmkv5RpkcdN3bqvN6RFkGqW6CGnLT7ldyjxW608ypp28PYrKW7DK3kG6_kFkTW7=
nj-MB37n0zjW220S_D7KKzg2W1wgd-H1htwD2W69T40p1TYSWGW708y767ZRQV2W7sVxLp2bK9X=
pW6YBx077q4H1RW1kldRr7blBdHW23_HbH7920clW68-W3J7686FCW3bwBFy5jg9t9W4PxC8j44=
8hL1W45S56F3sf_4PV7xpL46pZS60W2GD9PD47P_tyW973HVk1VXdNLVLWBZT8S_CdKW2R9dmN2=
Km5NXN3J2MmQ9XfwsD7s2GRztZ6f7HPsTx03" target=3D"_blank"><span style=3D"colo=
r:#007fa5">login page</span></a>.</span><span style=3D"font-size:11.5pt;fon=
t-family:&quot;Arial&quot;,sans-serif;color:dimgray"></span></p><p style=3D=
"margin-bottom:12.0pt;line-height:18.0pt"><span style=3D"font-size:11.5pt;f=
ont-family:&quot;Arial&quot;,sans-serif;color:dimgray">=C2=A0</span></p><p =
style=3D"margin-bottom:12.0pt;line-height:18.0pt"><em><span style=3D"font-s=
ize:11.5pt;font-family:&quot;Arial&quot;,sans-serif;color:dimgray">Question=
s? Please reach out to our govBenchmark team by sending an email to </span>=
</em><em><span style=3D"font-size:11.5pt;font-family:&quot;Arial&quot;,sans=
-serif;color:#007fa5"><a href=3D"mailto:govbenchmark@envisio.com" target=3D=
"_blank"><span style=3D"color:#007fa5">govbenchmark@envisio.com</span></a><=
/span></em><em><span style=3D"font-size:11.5pt;font-family:&quot;Arial&quot=
;,sans-serif;color:dimgray">, or check out our </span></em><em><span style=
=3D"font-size:11.5pt;font-family:&quot;Arial&quot;,sans-serif;color:#007fa5=
"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W5QH=
3YV3cgvMnW8Mfx458NTjLr0/5/f18dQhb0SbTK8XJ9l8W9kpyTt6ghkRDVWs4R057H86zW2KBfj=
g6mdtrJW8X0XFQ1WwpB4W31H3Ss55FSvFW8Wm1rS5m3NRqW634yBw50Cxs9W95S0C17dDWFFW63=
BbZ45v7rlKW3LrbbS964cbDW4shQzX50SXT6W61SSZm7mG7sDW51vX4y6H2lLvW2h82nD8hTJV4=
W8lwVXY8hS296N7bj1_4FCG-jW5rC5Q63l6qq3VKDygZ2KFZxKW3-b1_01VJww0W7YkMQV97rB1=
bW8yxW4B3LrljvW36_WFZ7hQFgJW6TWvy46R1QcjN5zHTzgflXyGW3Krmvn3MrtgpW3Myr828pN=
8fkW36T5nB28DKCNW3N26lG8pN2W7W5JNgsN93_ZM0W8mPqH897k6MrW5z5Hfl62JPs5W6M_5N6=
5v-TtRW7wV0lT3Cdk0dW3wv7Sm6xZN02W2RvPp52Qy1vMVN84gC6L2M9TW2cyH2G5vXpVNW6ZlT=
yS8zpqKfW6zNbyD4fKJkbW8dnXJY8SZLnvW9jNWG67D7HxjW1Dy_KC2FFR__f31NNg004" targ=
et=3D"_blank"><span style=3D"color:#007fa5">FAQ page</span></a>.</span></em=
><span style=3D"font-size:11.5pt;font-family:&quot;Arial&quot;,sans-serif;c=
olor:dimgray"></span></p></div></div></td></tr></table></td></tr><tr><td wi=
dth=3D"100%" valign=3D"top" style=3D"width:100.0%;padding:0cm 0cm 0cm 0cm">=
<table class=3D"MsoNormalTable" border=3D"0" cellspacing=3D"0" cellpadding=
=3D"0" width=3D"100%" style=3D"width:100.0%;border-collapse:collapse"><tr><=
td width=3D"100%" valign=3D"top" style=3D"width:100.0%;padding:0cm 0cm 0cm =
0cm"></td></tr></table></td></tr><tr><td width=3D"100%" valign=3D"top" styl=
e=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"><table class=3D"MsoNormalTable" =
border=3D"0" cellspacing=3D"0" cellpadding=3D"0" width=3D"100%" style=3D"wi=
dth:100.0%;border-collapse:collapse"><tr><td width=3D"100%" valign=3D"top" =
style=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"><div><div id=3D"hs_cos_wrapp=
er_Social_Sharing"><p class=3D"MsoNormal" align=3D"right" style=3D"text-ali=
gn:right;line-height:18.0pt"><span style=3D"font-size:11.5pt;font-family:&q=
uot;Arial&quot;,sans-serif;color:dimgray"><a href=3D"http://www.envisio.com=
/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*N1-fkDJrlXTjN2Pkj_TgpSJz0/5/f18dQhb0S1W=
d2RLJHxV1xT8T3wtBbPW2ZG9nv1QbKZpN2RGWxdsYmQTW4ZW1Mp6Qw1p1W4pK6yr7rLjSTW4sGV=
VV4vkW-sW8csZh24vB0S4VTvdL34sVKncW8Y5vLW3QfLGSW5dprWR9k-1sfW6pg--_6gnvxpN3r=
DF7x1dfB3W1XJpcR7wLvD4W1j4hjB2ygW4qW7544nj1dbZZ8W4mbclH5kf2KjW92XffX4jlT2cW=
9bmHMQ5kdRj4W1l6bVF1mpMLDW3Zb19z8h2c3nW19-jxh34W9RnW7NGwR02tdWQkW59mFqx2JKt=
6-W50s_W760JNZBW8x5PDc9fPKhnW36fwqR2sSdVmW68f_QP4S-4V9W6rXk2R4zPTrYW6j53215=
vK2lzW7GYkyB3qRP-WVnw24L5ChVLnW40zn972-sxzNW7krZ3Z1cn8zBVqQ1cY6sCLh-N2cXF9p=
llc7FW8Kcv5x37P4fVW3V5rg89b2LQNW80vZCg5LnrhDN3_4BdwyFN93W4jYN6B5NyMfqW3V_Pp=
m6Q60yXW2Vvrvn835My5VJjtgQ92P6Q_W4nDvms6td5Y9W8PG4wb8mWFQBW2d4k7h7vfRtLW97s=
5Ds7_DZfVW45NqWw969TSmW8qZ5Fp87JV39W6dY0gX8px95fW2lgtf_3v9MFnW1LpPgt5QKGy8W=
96QzQJ3dDlJrW7wTQ_y7SfWQ8W1FlNbV6-xZRhf7jDJ8H04" target=3D"_blank"><span st=
yle=3D"border:none windowtext 1.0pt;padding:0cm;text-decoration:none"><img =
border=3D"0" width=3D"24" style=3D"width:.25in" id=3D"_x0000_i1028" src=3D"=
https://static.hubspot.com/final/img/common/icons/social/facebook-24x24.png=
" alt=3D"Share on Facebook"></span></a>=C2=A0<a href=3D"http://www.envisio.=
com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W4_DPVY7YwkLJVC5hZ77krM9W0/5/f18dQhb=
0S1Wd2WHPHWV1xrKq70YxqPW63bxTM2rhzgQN2RGWzCY5r6mW9hSN0g5XT50PW2s9V5t6KS9_vW=
1L5zJx7QlmSLW3ys25x8wg-w6W3rYKNR7GJyZ_W874VwL5b39KTW4zx2sC4CZFPgW6FCzCl593x=
5hW3kR9t971X18-W8WmktQ1KkQxrW7f3Y8k63M7tjW7-Rxnb7N3XcfW1Rmz4C3CrJ1PW6F0nbh5=
Zg8gCW6mlMV04_6NcwV4M2R158fpmYW8c1fKN4dB8-HW47rypR35JfSRN8rQb5J78wpzVc8cgw7=
1dLzMN1YfrJqd_6jmN1NCc_xnQT09W7ZmkLR7jnF4QW744QZv5FD5f0N63zGqdKxK5pW49zyS07=
bg4QPW2_bhlH5mh5sRW4NkQwx2NjBMDW5ybRZd2YHtVKW2dW2fb6Ln_sbW3vzwhj623xbkW8SnP=
Mf6t_15zW3m0mtk7nJQV8W3ZxV-l5tkrXxVgsPd44HD1tlW2RVyTW1x65xLW92bkwW2W-PXdW7Y=
1ww75fmXS7N283QT-pGy4JW5kHVqG3vTXhSW2T3kpH6DfcPSW172cCL1ZTf-LW8Xn6987JvmFCW=
45XlrT1W_yRcW1JNjV61nRsCrW4W8Gxy919VzvW6Lc6tF6KwJ6ZW6-dGBp21bFFGW2hjjZc5Rsz=
snW97xfWv2hxQcbW9k4P38679lcpW8pG-_N4m_SymW2nqQxy18tTGtf4FfX4R03" target=3D"=
_blank"><span style=3D"border:none windowtext 1.0pt;padding:0cm;text-decora=
tion:none"><img border=3D"0" width=3D"24" style=3D"width:.25in" id=3D"_x000=
0_i1029" src=3D"https://static.hubspot.com/final/img/common/icons/social/li=
nkedin-24x24.png" alt=3D"Share on LinkedIn"></span></a>=C2=A0<a href=3D"htt=
p://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W5vf57m6fvpzPW2s3Ll8=
3GM5MX0/5/f18dQhb0S4002RLJHRMWWj76gg1Y-N4RS864M5nfdW4F3qnv3LnybcW2gHmRt7Cwr=
xFW1cmgyR6JHpcTW7mPCpV7QllrZV80J1v6rrQGxW503SND4-xbncW7VZdRP5k5tmGW31WqLJ18=
NtVmW9hF0k13L_sBfW5Swg0g2g5VxrW93GhSf1TLT1QW6BRcxV4RR5PXW7nsSGx5VN1hpVDyC34=
4ZF7rYW8fMnd-2nk5HqW21shfr8kd5-SW5mcz_j2HzCcrN5D_SrQVj2q_W5ccgcQ6StJ23W7Smr=
902qN3kzW4MPcT-1tlbk4W1vQTK23gXx9LW28cLHk1szXQPW4WqW-q82gdH8W2fGbpY764QBCW3=
HHnrD6fTFNpW8rCJ4x1lYpmQW7l1Gz964HDJyW2w7Fxl5hDQlLW1VGjMR9kL4xbW7gPRtn13Tns=
zVgccMM65yk_9W65f5pf8GJlKYMTRQJqmpl9KW1jVnLn7qVZHBVtjddF4c5pJXMV04RsYSnPHW3=
JMswl2yZvKVVYWxf13Ln5-0W7DD2P97Pgt7dV6RRnC6K8sGlW3Rj5lB7G7xzBW7MPR4H7Y0NB0W=
6gQ1Kc6BBqZTW1s80bG42MlTBW3j0K0_8QzcSdW9b5fDF7_2mbQVx2S0S8mvn6bW6x8wvF7lmrs=
FVXzktJ4g17t4W7qQyxv2v0PFYW7_JFN83l13FNVd72jz9m5GsBN4pWrGySzxVSW87dD-T2TnYG=
mW8Ty_bp6NwKl2N8cQpZQzsmhVN99qYVdT367wN35tyldB3gtpf1FSXDT03" target=3D"_bla=
nk"><span style=3D"border:none windowtext 1.0pt;padding:0cm;text-decoration=
:none"><img border=3D"0" width=3D"24" style=3D"width:.25in" id=3D"_x0000_i1=
030" src=3D"https://static.hubspot.com/final/img/common/icons/social/twitte=
r-24x24.png" alt=3D"Share on Twitter"></span></a>=C2=A0<a href=3D"http://ww=
w.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*V2pMcN6CX6n8W5CK9Z05vvVth0=
/5/f18dQhb0S65M2WHPNlVWV7hR1xqK9MW6tp5NM5gGFjNW84GyQg1xq93pW9lzfK653JrjYW1k=
Dy1B2J9k17W3LFGzr6fBpcKMhjmFBGrcdtV8fQ134vCgXWV9zG0_5f8j2dVkwyJN6YFr_3W43p-=
Gf8pvzSDW54SqBJ7rJXH-W2y821s5YWg4MW3WzkVL4NqGPzW74YwF-2ByMQMW25bj133VB162W8=
_yr_Q3v5qFDW2Qxg196jTtv_VMFm0j2F0rmlW34N7Sl5FSl3jDDmHJ7QkKKW4Jln973xC7ybW6Q=
P86r1pF3JmW4_Ph4v7qL2gKW6QrsbW4n2CnrTl7-68w3PnmW1zY01B2n-0SJW3dM30p7tD5NsW2=
XFCcl2hHFzmW4R9KSg6SlNjCN1RQTBPxj_B3W69Jgl81sPmwGW6TjX1b7cwv2WW6SXHZ-89XJbw=
W7h6PXL64z3X9W42z34C5Ngw-RW3pcSqW1py-3zN3DtTWPPGxmcW3xNWbH227KF_W3y7GM-478w=
y9W84bNJT6LBrVBW9cMn_x4hxDKVW6cb60V4YBkYtW63Yg8b26WZcsW42Xtw016HzklW1rq93y6=
gcSMxW3-CjqJ3qYh49W7zH87D28_svWW3Fyjdw7npTYsW9jGVll8G4SKrW658Mlm5QcFt_W5QZv=
192hhWy0W8YVs9K8BclbcW70bY2j1Jls6_W5q01Df9b1nZT111" target=3D"_blank"><span=
 style=3D"border:none windowtext 1.0pt;padding:0cm;text-decoration:none"><i=
mg border=3D"0" width=3D"24" style=3D"width:.25in" id=3D"_x0000_i1031" src=
=3D"https://static.hubspot.com/final/img/common/icons/social/googleplus-24x=
24.png" alt=3D"Share on Google+"></span></a></span></p></div></div></td></t=
r></table></td></tr></table></div></td></tr></table></div></td></tr><tr><td=
 valign=3D"top" style=3D"padding:0cm 0cm 0cm 0cm"><div align=3D"center"><ta=
ble class=3D"MsoNormalTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"=
0" width=3D"100%" style=3D"width:100.0%;background:#eeeeee;border-collapse:=
collapse" id=3D"footerTable"><tr><td width=3D"100%" colspan=3D"12" valign=
=3D"top" style=3D"width:100.0%;padding:15.0pt 15.0pt 15.0pt 15.0pt"><table =
class=3D"MsoNormalTable" border=3D"0" cellspacing=3D"0" cellpadding=3D"0" w=
idth=3D"100%" style=3D"width:100.0%;border-collapse:collapse"><tr><td width=
=3D"100%" valign=3D"top" style=3D"width:100.0%;padding:0cm 0cm 0cm 0cm"><di=
v><p align=3D"center" style=3D"margin-bottom:12.0pt;text-align:center;line-=
height:16.1pt"><span style=3D"font-size:9.0pt;font-family:&quot;Verdana&quo=
t;,sans-serif;color:#007da5">Envisio Solutions Inc. =C2=A0=C2=A0250-13777 C=
ommerce Parkway =C2=A0 =C2=A0Richmond =C2=A0British Columbia =C2=A0=C2=A0V6=
V 2X3 =C2=A0=C2=A0Canada <br><br>You received this email because you are su=
bscribed to Newsletter from Envisio Solutions Inc. . <br><br>Update your <a=
 href=3D"http://www.envisio.com/hs/manage-preferences/unsubscribe-test?d=3D=
eyJlYSI6Im1kYW5pZWxAZW52aXNpby5jb20iLCJlYyI6Miwic3Vic2NyaXB0aW9uSWQiOjEsImV=
0IjoxNTEwNzkwOTk1NDk1LCJldSI6ImU3ZTBjOWI4LThjMWEtNDFjZC1hZDJhLTYyMjY5ZDNjZW=
YzNCJ9&amp;v=3D1&amp;_hsenc=3Dp2ANqtz--KaksqBydow_ubOEOe-EUMX4hIIP_bnjvhej6=
bukdIRnLOrxuc3C2v1kl7bnOGs7OZDOy4lnNT9MSsX63T2dt2xGLX9w&amp;_hsmi=3D2" targ=
et=3D"_blank"><span style=3D"color:#007da5">email preferences</span></a> to=
 choose the types of emails you receive. <br><br>=C2=A0<a href=3D"http://ww=
w.envisio.com/hs/manage-preferences/unsubscribe-all-test?d=3DeyJlYSI6Im1kYW=
5pZWxAZW52aXNpby5jb20iLCJlYyI6Miwic3Vic2NyaXB0aW9uSWQiOjEsImV0IjoxNTEwNzkwO=
Tk1NDk1LCJldSI6ImU3ZTBjOWI4LThjMWEtNDFjZC1hZDJhLTYyMjY5ZDNjZWYzNCJ9&amp;v=
=3D1&amp;_hsenc=3Dp2ANqtz--KaksqBydow_ubOEOe-EUMX4hIIP_bnjvhej6bukdIRnLOrxu=
c3C2v1kl7bnOGs7OZDOy4lnNT9MSsX63T2dt2xGLX9w&amp;_hsmi=3D2" target=3D"_blank=
"><span style=3D"color:#007da5">Unsubscribe from all future emails</span></=
a> =C2=A0 </span></p></div></td></tr></table></td></tr><tr><td style=3D"pad=
ding:0cm 0cm 0cm 0cm"></td><td style=3D"padding:0cm 0cm 0cm 0cm"></td><td s=
tyle=3D"padding:0cm 0cm 0cm 0cm"></td><td style=3D"padding:0cm 0cm 0cm 0cm"=
></td><td style=3D"padding:0cm 0cm 0cm 0cm"></td><td style=3D"padding:0cm 0=
cm 0cm 0cm"></td><td style=3D"padding:0cm 0cm 0cm 0cm"></td><td style=3D"pa=
dding:0cm 0cm 0cm 0cm"></td><td style=3D"padding:0cm 0cm 0cm 0cm"></td><td =
style=3D"padding:0cm 0cm 0cm 0cm"></td><td style=3D"padding:0cm 0cm 0cm 0cm=
"></td><td style=3D"padding:0cm 0cm 0cm 0cm"></td></tr></table></div></td><=
/tr></table></div></td></tr></table></div><p class=3D"MsoNormal"><span styl=
e=3D"font-family:&quot;Arial&quot;,sans-serif"><img border=3D"0" width=3D"1=
" height=3D"1" style=3D"width:.0069in;height:.0069in" id=3D"_x0000_i1032" s=
rc=3D"http://www.envisio.com/e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*W1ZP-6Y7bt=
x6pN2p3BQzWx3Lr0/5/f18dQhb0KdhFBRTRVW5kgQqJ25yxJ6N2zQNWY3k99nVslmXx56tc0yW6=
VzwTx76c36kN4rY3n_xY9XwW5DpS2k4vgKM1W19mJ8q3nh5k9W8KzghL8V1J5BW3XjVf4201bP-=
W6vc40h8sTwp1W91JwMn2CPVg1T2vdM8TbwvM103"></span></p></div>';
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

        $copyTo = 137; // 177995;

        $mergeService->merge($from, $copyTo);

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

    protected function getSubscriptions()
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
        $lapsedService->setSubscriptionModel($this->getSubscriptionModel());
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
     * @return \Mrss\Model\BenchmarkHeading
     */
    public function getBenchmarkHeadingModel()
    {
        return $this->getServiceLocator()->get('model.benchmark.heading');
    }

    /**
     * @deprecated
     */
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

    protected function getCurrentSubscription()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $study = $this->currentStudy();
        $college = $this->currentCollege();

        return $subscriptionModel->findCurrentSubscription($study, $college->getId());
    }

    /**
     * During merging of NCCBP and Workforce to modules/sections of the same study,
     * set old memberships up
     * @deprecated
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

    /**
     * @deprecated
     */
    public function importWfAction()
    {
        $this->longRunningScript();
        $year = $this->params()->fromQuery('year', 2017);

        $importer = $this->getServiceLocator()->get('service.import.workforce.data');

        $importer->import($year);

        die('test');
    }

    /**
     * @deprecated
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getWfDb()
    {
        return $this->getServiceLocator()->get('workforce-db');
    }

    public function importDataAction()
    {
        $this->longRunningScript();
        $importer = $this->getImportDataService();

        $form = $importer->getForm();

        // Handle the form
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $filename = $data['file']['tmp_name'];

                $importer->setFile($filename);
                $importer->import($this->getServiceLocator());

                $this->flashMessenger()->addSuccessMessage(
                    "Your import was processed. " .
                    "Please review the <a href='/admin/changes'>recent data changes</a>."
                );
                return $this->redirect()->toUrl('/tools/import-data');
            }
        }

        //

        return array(
            'form' => $form
        );
    }

    public function uploadDataAction()
    {
    }

    /**
     * @return \Mrss\Service\Import\Data
     */
    protected function getImportDataService()
    {
        return $this->getServiceLocator()->get('service.import.data');
    }
}
