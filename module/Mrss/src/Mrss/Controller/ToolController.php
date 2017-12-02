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

                $content = $this->getTestHtml();

                //$content = "This is a <b>simple</b> HTML email.";
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

                $message->getHeaders()->get('content-type')->setType('multipart/alternative');


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
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org=
/TR/xhtml1/DTD/xhtml1-strict.dtd"><!-- start coded_template: id:5437764569 =
path:generated_layouts/5437647422.html --><html xmlns=3D"http://www.w3.org/=
1999/xhtml" xmlns:v=3D"urn:schemas-microsoft-com:vml" xmlns:o=3D"urn:schema=
s-microsoft-com:office:office"><head>
        <title>Welcome to govBenchmark</title>
        <meta property=3D"og:title" content=3D"Welcome to govBenchmark">
        <meta name=3D"twitter:title" content=3D"Welcome to govBenchmark">
        <meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Du=
tf-8">
       =20
        <style type=3D"text/css" id=3D"hs-inline-css">
/*<![CDATA[*/
  /* everything in this node will be inlined */

  /* =3D=3D=3D=3D Page Styles =3D=3D=3D=3D */

  body, #backgroundTable {
      background-color: #eeeeee; /* Use body to determine background color =
*/
      font-family: sans-serif;
  }

  #templateTable {
      width: 600px;
      background-color: #ffffff;
      -webkit-font-smoothing: antialiased;
  }

  h1, .h1, h2, .h2, h3, .h3, h4, .h4, h5, .h5, h6, .h6 {
      display:block;
      font-weight:bold;
      line-height:100%;
      margin-top:0;
      margin-right:0;
      margin-bottom:10px;
      margin-left:0;
  }

  h1, .h1 {
      font-size:26px;
  }

  h2, .h2 {
      font-size:20px;
  }

  h3, .h3 {
      font-size:15px;
  }

  h4, .h4 {
      font-size:13px;
  }

  h5, .h5 {
      font-size:11px;
  }

  h6, .h6 {
      font-size:10px;
  }

  /* =3D=3D=3D=3D Header Styles =3D=3D=3D=3D */

  #headerTable {
      background-color: #eeeeee;
      color:#696969;
      font-family:sans-serif;
      font-size:10px;
      line-height:120%;
      text-align:right;
      border-collapse: separate !important;
      padding-right: 30px;
  }

  #headerTable a:link, #headerTable a:visited, /* Yahoo! Mail Override */ #=
headerTable a .yshortcuts /* Yahoo! Mail Override */{
      font-weight:normal;
      text-decoration:underline;
  }

  /* =3D=3D=3D=3D Template Wrapper Styles =3D=3D=3D=3D */

  #contentCell {
      padding: 10px 20px;
      background-color: #eeeeee;
  }

  #contentTableOuter {
      border-collapse: separate !important;

      background-color: #ffffff;
     =20
      box-shadow: 0px 1px rgba(0, 0, 0, 0.1);
     =20

      padding: 30px;
  }

  #contentTableInner {
      width: 600px;
  }

  /* =3D=3D=3D=3D Body Styles =3D=3D=3D=3D */

  .bodyContent {
      color:#696969;
      font-family:sans-serif;
      font-size: 15px;
      line-height:150%;
      text-align:left;
  }

  /* =3D=3D=3D=3D Column Styles =3D=3D=3D=3D */

  table.columnContentTable {
      border-collapse:separate !important;
      border-spacing:0;

      background-color: #ffffff;
  }

  td.columnContent {
      color:#696969;
      font-family:sans-serif;
      font-size:15px;
      line-height:120%;
      padding-top:20px;
      padding-right:20px;
      padding-bottom:20px;
      padding-left:20px;
  }

  /* =3D=3D=3D=3D Footer Styles =3D=3D=3D=3D */

  #footerTable {
      background-color: #eeeeee;
  }

  #footerTable a {
      color: #007DA5;
  }

  #footerTable {
      color:#007DA5;
      font-family:sans-serif;
      font-size:12px;
      line-height:120%;
      padding-top:20px;
      padding-right:20px;
      padding-bottom:20px;
      padding-left:20px;
      text-align:center;
  }

  #footerTable a:link, #footerTable a:visited, /* Yahoo! Mail Override */ #=
footerTable a .yshortcuts /* Yahoo! Mail Override */{
      font-weight:normal;
      text-decoration:underline;
  }

  .hs-image-social-sharing-24 {
      max-width: 24px;
      max-height: 24px;
  }

  /* =3D=3D=3D=3D Standard Resets =3D=3D=3D=3D */
  .ExternalClass{
      width:100%;
  } /* Force Hotmail to display emails at full width */
  .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass fon=
t, .ExternalClass td, .ExternalClass div {
      line-height: 100%;
  } /* Force Hotmail to display normal line spacing */
  body, table, td, p, a, li, blockquote{
      -webkit-text-size-adjust:100%;
      -ms-text-size-adjust:100%;
  } /* Prevent WebKit and Windows mobile changing default text sizes */
  table, td {
      mso-table-lspace:0pt;
      mso-table-rspace:0pt;
  } /* Remove spacing between tables in Outlook 2007 and up */
  img {
      vertical-align: bottom;
      -ms-interpolation-mode:bicubic;
  } /* Allow smoother rendering of resized image in Internet Explorer */

  /* Reset Styles */
  body {
      margin:0;
      padding:0;
  }
  table {
      border-collapse:collapse !important;
  }
  body, #backgroundTable, #bodyCell{
      height:100% !important;
      margin:0;
      padding:0;
      width:100% !important;
  }
  a:link, a:visited {
      border-bottom: none;
  }

  /* iOS automatically adds a link to addresses */
  /* Style the footer with the same color as the footer text */
  #footer a {
      color: #007DA5;;
      -webkit-text-size-adjust: none;
      text-decoration: underline;
      font-weight: normal
  }
/*]]>*/
</style>

        <style type=3D"text/css">
/*<![CDATA[*/
  /* =3D=3D=3D=3D Mobile Styles =3D=3D=3D=3D */

  /* Constrain email width for small screens */
  @media screen and (max-width: 650px) {
      table#backgroundTable {
          width: 95% !important;
      }

      table#templateTable {
          max-width:600px !important;
          width:100% !important;
      }

      table#contentTableInner {
          max-width:600px !important;
          width:100% !important;
      }

      /* Makes image expand to take 100% of width*/
      img {
          width: 100% !important;
          height: auto !important;
      }

      #contentCell {
          padding: 10px 10px !important;
      }

      #headerTable {
          padding-right: 15.0px !important;
      }

      #contentTableOuter {
          padding: 15.0px !important;
      }
  }

  @media only screen and (max-width: 480px) {
      /* =3D=3D=3D=3D Client-Specific Mobile Styles =3D=3D=3D=3D */
      body, table, td, p, a, li, blockquote{
          -webkit-text-size-adjust:none !important;
      } /* Prevent Webkit platforms from changing default text sizes */
      body{
          width:100% !important;
          min-width:100% !important;
      } /* Prevent iOS Mail from adding padding to the body */

      /* =3D=3D=3D=3D Mobile Reset Styles =3D=3D=3D=3D */
      td#bodyCell {
          padding:10px !important;
      }

      /* =3D=3D=3D=3D Mobile Template Styles =3D=3D=3D=3D */

      table#templateTable {
          max-width:600px !important;
          width:100% !important;
      }

      table#contentTableInner {
          max-width:600px !important;
          width:100% !important;
      }

      /* =3D=3D=3D=3D Image Alignment Styles =3D=3D=3D=3D */

      h1, .h1 {
          font-size:26px !important;
          line-height:125% !important;
      }

      h2, .h2 {
          font-size:20px !important;
          line-height:125% !important;
      }

      h3, .h3 {
          font-size:15px !important;
          line-height:125% !important;
      }

      h4, .h4 {
          font-size:13px !important;
          line-height:125% !important;
      }

      h5, .h5 {
          font-size:11px !important;
          line-height:125% !important;
      }

      h6, .h6 {
          font-size:10px !important;
          line-height:125% !important;
      }

      .hide {
          display:none !important;
      } /* Hide to save space */

      /* =3D=3D=3D=3D Body Styles =3D=3D=3D=3D */

      td.bodyContent {
          font-size:16px !important;
          line-height:145% !important;
      }

      /* =3D=3D=3D=3D Footer Styles =3D=3D=3D=3D */

      td#footerTable {
          padding-left: 0px !important;
          padding-right: 0px !important;
          font-size:12px !important;
          line-height:145% !important;
      }

      /* =3D=3D=3D=3D Image Alignment Styles =3D=3D=3D=3D */

      table.alignImageTable {
          width: 100% !important;
      }

      td.imageTableTop {
          display: none !important;
          /*padding-top: 10px !important;*/
      }
      td.imageTableRight {
          display: none !important;
      }
      td.imageTableBottom {
          padding-bottom: 10px !important;
      }
      td.imageTableLeft {
          display: none !important;
      }

      /* =3D=3D=3D=3D Column Styles =3D=3D=3D=3D */

      td.column {
          display: block !important;
          width: 100% !important;
          padding-top: 0 !important;
          padding-right: 0 !important;
          padding-bottom: 0 !important;
          padding-left: 0 !important;
      }

      td.columnContent {
          font-size:14px !important;
          line-height:145% !important;

          padding-top: 10px !important;
          padding-right: 10px !important;
          padding-bottom: 10px !important;
          padding-left: 10px !important;
      }

      #contentCell {
          padding: 10px 0px !important;
      }

      #headerTable {
          padding-right: 15.0px !important;
      }

      #contentTableOuter {
          padding: 15.0px !important;
      }
  }
/*]]>*/
</style>

        <!-- http://www.emailon@cid.com/blog/details/C13/ensure_that_your_e=
ntire_email_is_rendered_by_default_in_the_iphone_ipad -->
        <!--                                                               =
                                                      -->
        <!--                                                               =
                                                      -->
        <!--                            _/    _/            _/          _/_=
/_/                        _/                         -->
        <!--                           _/    _/  _/    _/  _/_/_/    _/    =
    _/_/_/      _/_/    _/_/_/_/                      -->
        <!--                          _/_/_/_/  _/    _/  _/    _/    _/_/ =
   _/    _/  _/    _/    _/                           -->
        <!--                         _/    _/  _/    _/  _/    _/        _/=
  _/    _/  _/    _/    _/                            -->
        <!--                        _/    _/    _/_/_/  _/_/_/    _/_/_/   =
 _/_/_/      _/_/        _/_/                         -->
        <!--                                                               =
_/                                                    -->
        <!--                                                              _=
/                                                     -->
        <!--                                                               =
                                                      -->
        <!--                                                 Extra White Sp=
ace!                                                  -->
        <!--                                                               =
                                                      -->
        <!-- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - =
- - - - - - - - - - - - - - - - - - - - - - - - - - - -->
       =20
        <!--[if gte mso 9]>
          <xml>
            <o:OfficeDocumentSettings>
              <o:AllowPNG/>
              <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
          </xml>
        <![endif]-->
    <meta name=3D"generator" content=3D"HubSpot"><meta property=3D"og:url" =
content=3D"http://www.envisio.com/-temporary-slug-824cf9ba-060d-44e4-97c0-9=
872e6481982?hs_preview=3DqUeYhNAt-5437638635"></head>
    <body class=3D"" style=3D"background-color:#eeeeee; font-family:sans-se=
rif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; height:100% =
!important; margin:0; padding:0; width:100% !important" leftmargin=3D"0" ma=
rginwidth=3D"0" topmargin=3D"0" marginheight=3D"0" offset=3D"0" bgcolor=3D"=
#eeeeee" height=3D"100% !important" width=3D"100% !important">
        <!-- Preview text (text which appears right after subject) -->
        <div id=3D"preview_text" style=3D"display:none;font-size:1px;color:=
#eeeeee;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hid=
den;">
            Welcome to Envisio govBenchmark! You are now part of a communit=
y of 100+ local government members across North America.
        </div>

        <!--  The  backgroundTable table manages the color of the backgroun=
d and then the templateTable maintains the body of=20
        the email template, including preheader & footer. This is the only =
table you set the width of to, everything else is set to=20
        100% and in the CSS above. Having the width here within the table i=
s just a small win for Lotus Notes. -->

        <!-- Begin backgroundTable --> =20
        <table align=3D"center" bgcolor=3D"#eeeeee" border=3D"0" cellpaddin=
g=3D"0" cellspacing=3D"0" height=3D"100% !important" width=3D"100% !importa=
nt" id=3D"backgroundTable" style=3D"-webkit-text-size-adjust:100%; -ms-text=
-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-colla=
pse:collapse !important; background-color:#eeeeee; font-family:sans-serif; =
height:100% !important; margin:0; padding:0; width:100% !important">
            <tbody><tr>
                <td align=3D"center" valign=3D"top" id=3D"bodyCell" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; height:100% !important; margin:0; padding:0;=
 width:100% !important" height=3D"100% !important" width=3D"100% !important=
"> <!-- When nesting tables within a TD, align center keeps it well, center=
ed. -->
                    <!-- Begin Template Container -->
                    <!-- This holds everything together in a nice container=
 -->
                    <table border=3D"0" cellpadding=3D"0" cellspacing=3D"0"=
 id=3D"templateTable" style=3D"-webkit-text-size-adjust:100%; -ms-text-size=
-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:c=
ollapse !important; width:600px; background-color:#ffffff; -webkit-font-smo=
othing:antialiased" width=3D"600" bgcolor=3D"#ffffff">
                        <tbody><tr>
                            <td align=3D"center" valign=3D"top" style=3D"-w=
ebkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0p=
t; mso-table-rspace:0pt">
                                <!-- Begin Template Preheader -->
                                <div class=3D"header-container-wrapper">
</div><table border=3D"0" cellpadding=3D"0" cellspacing=3D"0" width=3D"100%=
" id=3D"headerTable" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-=
adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; background-color:#=
eeeeee; color:#696969; font-family:sans-serif; font-size:10px; line-height:=
120%; text-align:right; border-collapse:separate !important; padding-right:=
30px" bgcolor=3D"#eeeeee" align=3D"right">
                                    <tbody><tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"12" width=3D"100.0%" class=3D" column" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; width:100.0%; text-align:left; padding:0; fo=
nt-family:sans-serif; font-size:15px; line-height:1.5em; color:#696969" ali=
gn=3D"left">

<div class=3D"widget-span widget-type-email_view_as_web_page " style=3D"" d=
ata-widget-type=3D"email_view_as_web_page">

</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<!--end header wrapper -->
                                </tbody></table>
                                <!-- End Template Preheader -->
                            </td>
                        </tr>
                        <tr>
                            <td align=3D"center" valign=3D"top" id=3D"conte=
ntCell" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; =
mso-table-lspace:0pt; mso-table-rspace:0pt; padding:10px 20px; background-c=
olor:#eeeeee" bgcolor=3D"#eeeeee">
                                <!-- Begin Template Wrapper -->
                                <!-- This separates the preheader which usu=
ally contains the "open in browser, etc" content
                                from the actual body of the email. Can alte=
rnatively contain the footer too, but I choose not
                                to so that it stays outside of the border. =
-->
                                <table border=3D"0" cellpadding=3D"0" cells=
pacing=3D"0" width=3D"100%" id=3D"contentTableOuter" style=3D"-webkit-text-=
size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0pt; mso-tabl=
e-rspace:0pt; border-collapse:separate !important; background-color:#ffffff=
; box-shadow:0px 1px rgba(0, 0, 0, 0.1); padding:30px; border:1px solid #c8=
c8c8; border-bottom:1px solid #a8a8a8" bgcolor=3D"#ffffff">
                                    <tbody><tr>
                                        <td align=3D"center" valign=3D"top"=
 style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-tab=
le-lspace:0pt; mso-table-rspace:0pt">
                                            <div class=3D"body-container-wr=
apper">
</div><table border=3D"0" cellpadding=3D"0" cellspacing=3D"0" id=3D"content=
TableInner" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:10=
0%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse !i=
mportant; width:600px" width=3D"600">
                                                <tbody><tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"6" width=3D"50.0%" class=3D" column" style=3D=
"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace=
:0pt; mso-table-rspace:0pt; width:50.0%; text-align:left; padding:0; font-f=
amily:sans-serif; font-size:15px; line-height:1.5em; color:#696969" align=
=3D"left">

<div class=3D"widget-span widget-type-logo " style=3D"" data-widget-type=3D=
"logo">
<div class=3D"layout-widget-wrapper">
<div id=3D"hs_cos_wrapper_module_1508213813756379" class=3D"hs_cos_wrapper =
hs_cos_wrapper_widget hs_cos_wrapper_type_logo" style=3D"color: inherit; fo=
nt-size: inherit; line-height: inherit;" data-hs-cos-general-type=3D"widget=
" data-hs-cos-type=3D"logo"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZ=
s5kvP7SVM5lLF96Hybr0/*W35l1ps3-PwTzW2ZyLGY1wYhxy0/5/f18dQhb0SmhY8YXMd0W9kpy=
Tt6ghkRDVWs4R057z_B8W5r8vy28yym7NW5yMB6q5sNtXpW8rDxGf8ttb3HW8mp2bw8p-Vr0W65=
kbk08sZ817W8q5dtq1p84vVW1QfXSP8sZ4WSW67b7w67hYCG4W3N1Lh352SLKTW64jzh47-JXPK=
W23Gh7k3bPyFLW3Cblxq5RlCDgW3LZN2x8vr5SsW62nKnz3s88CtW3Tmhxm5wP7thVRxxws3JYp=
DrW8wwpp65Cy6j4W8nZCpK3n9W34VP9g1_5TW9l1W3dNSsg3VTm7lVV-yf65QZ404W3Lty8R3C8=
_0bV-v7BL3y7YmZW8lGJ5S3BvPNjW3pJ9BM37Q5BpW228cJg53NYtcW4PLwTV7pGBLqW1by4Pn2=
jH3TXW7B-p2f8ltgwJV84rVz6LzXZsW9h7xlS4gMWr5W6yncsP2RWhGGW8pj_Df2qwSMlW4Fyg3=
S6G6v46N4Pv86f5XSm_f10PpzN02" id=3D"hs-link-module_1508213813756379" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; border-width:=
0px; border:0px" data-hs-link-id=3D"0" target=3D"_blank"><img src=3D"https:=
//cdn2.hubspot.net/hub/364927/hubfs/Images-New/envisio-footer-logo.png?t=3D=
1511477620377&amp;width=3D299&amp;name=3Denvisio-footer-logo.png" class=3D"=
hs-image-widget " style=3D"vertical-align:bottom; -ms-interpolation-mode:bi=
cubic; max-height:71px; max-width:299px; border-width:0px; border:0px" widt=
h=3D"299" alt=3D"Envisio logo" title=3D"Envisio logo" srcset=3D"https://cdn=
2.hubspot.net/hub/364927/hubfs/Images-New/envisio-footer-logo.png?t=3D15114=
77620377&amp;width=3D299&amp;name=3Denvisio-footer-logo.png 299w, https://c=
dn2.hubspot.net/hub/364927/hubfs/Images-New/envisio-footer-logo.png?t=3D151=
1477620377&amp;width=3D598&amp;name=3Denvisio-footer-logo.png 598w" sizes=
=3D"(max-width: 299px) 100vw, 299px"></a></div>
</div><!--end layout-widget-wrapper -->
</div><!--end widget-span -->
   </td>
<td valign=3D"top" colspan=3D"6" width=3D"50.0%" class=3D" column" style=3D=
"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace=
:0pt; mso-table-rspace:0pt; width:50.0%; text-align:left; padding:0; font-f=
amily:sans-serif; font-size:15px; line-height:1.5em; color:#696969" align=
=3D"left">

<div class=3D"widget-span widget-type-logo " style=3D"" data-widget-type=3D=
"logo">
<div class=3D"layout-widget-wrapper">
<div id=3D"hs_cos_wrapper_module_1508213837506380" class=3D"hs_cos_wrapper =
hs_cos_wrapper_widget hs_cos_wrapper_type_logo" style=3D"color: inherit; fo=
nt-size: inherit; line-height: inherit;" data-hs-cos-general-type=3D"widget=
" data-hs-cos-type=3D"logo"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZ=
s5kvP7SVM5lLF96Hybr0/*W5MrH5C26-l77W7DqBL072LNRM0/5/f18dQhb0SmhX8YXMz-W9kpy=
Tt6ghkRDVWs4R057skKTW5r8vy28yym7NW5yMB6q5sNtXpW8rDxGf8tBV55W5xd97M1mQsyjVRb=
qhJ5wL96LW1sL3M03MybvyV4LnFC62x5yTW4P1qTD2yJF1WVYT2jk6P4lCXW6hF8n169NG1wW6P=
r3nq18g5kSW8sYvvl5tGlT_W7cvxRc7wT8GgW4bQW_q6c41FMW20VHXM6jN0NdW2XrStF2G7jz5=
W58BXty1PBZX8W8Q65lS6fhY0NVx-7lH7BvCWRW1Hphc52Fp-VCW1Y_4W61CXjCWW8Z1Jzs4kcY=
52Vs31Vt2WZ-ryVw_QLC1zLZPxVr3Jgp7zPg7lVyY5G35tb4RZW3HR_B28csLSqW10360J30WVz=
4W5L1GjL5zKhtzVxw8zd4Pvq-YW49FShz6_S05NW46r0CS89HnyHW6Tfmrz6t7f6JW2trFNk3tY=
y17W2WBfjJ4Q-5SVW4Hzl-X6002Wf111" id=3D"hs-link-module_1508213837506380" st=
yle=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; border-wid=
th:0px; border:0px" data-hs-link-id=3D"0" target=3D"_blank"><img src=3D"htt=
ps://cdn2.hubspot.net/hub/364927/hubfs/Envisio%20Logo/govbenchmark-logo-lg.=
png?t=3D1511477620377&amp;width=3D270&amp;name=3Dgovbenchmark-logo-lg.png" =
class=3D"hs-image-widget " style=3D"vertical-align:bottom; -ms-interpolatio=
n-mode:bicubic; max-height:58px; max-width:270px; border-width:0px; border:=
0px" width=3D"270" alt=3D"govBenchmark" title=3D"govBenchmark" srcset=3D"ht=
tps://cdn2.hubspot.net/hub/364927/hubfs/Envisio%20Logo/govbenchmark-logo-lg=
.png?t=3D1511477620377&amp;width=3D270&amp;name=3Dgovbenchmark-logo-lg.png =
270w, https://cdn2.hubspot.net/hub/364927/hubfs/Envisio%20Logo/govbenchmark=
-logo-lg.png?t=3D1511477620377&amp;width=3D540&amp;name=3Dgovbenchmark-logo=
-lg.png 540w" sizes=3D"(max-width: 270px) 100vw, 270px"></a></div>
</div><!--end layout-widget-wrapper -->
</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"12" width=3D"100.0%" class=3D" column" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; width:100.0%; text-align:left; padding:0; fo=
nt-family:sans-serif; font-size:15px; line-height:1.5em; color:#696969" ali=
gn=3D"left">

<div class=3D"widget-span widget-type-email_body " style=3D"" data-widget-t=
ype=3D"email_body">
<div id=3D"hs_cos_wrapper_hs_email_body" class=3D"hs_cos_wrapper hs_cos_wra=
pper_widget hs_cos_wrapper_type_rich_text" style=3D"color: inherit; font-si=
ze: inherit; line-height: inherit;" data-hs-cos-general-type=3D"widget" dat=
a-hs-cos-type=3D"rich_text"><p style=3D"margin-bottom: 1em; -webkit-text-si=
ze-adjust:100%; -ms-text-size-adjust:100%"><span style=3D"font-weight: 400;=
 line-height: 1.5em;">Strategy Enthusiast,</span></p>
<p style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-siz=
e-adjust:100%"><span style=3D"background-color: transparent; color: inherit=
; font-size: inherit;">We=E2=80=99re very excited to welcome you to the gov=
Benchmark family! By signing up, you are joining a community of members acr=
oss North America looking to improve their government=E2=80=99s performance=
 through the use of advanced benchmarking software.</span></p>
<p style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-siz=
e-adjust:100%; line-height:1.5em"><span style=3D"background-color: transpar=
ent; color: inherit; font-size: inherit;">Your username to log into govBenc=
hmark will be your email address. To sign in, please first click on the bel=
ow link to set up your password.</span></p>
<center><!--HubSpot Call-to-Action Code --><span class=3D"hs-cta-wrapper" d=
ata-hs-img-pg=3D"871f163e-d87b-46d4-93f9-4cc558f2636e" id=3D"hs-cta-wrapper=
-871f163e-d87b-46d4-93f9-4cc558f2636e"><span class=3D"hs-cta-node hs-cta-87=
1f163e-d87b-46d4-93f9-4cc558f2636e" id=3D"hs-cta-871f163e-d87b-46d4-93f9-4c=
c558f2636e"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96H=
ybr0/*W3-JJWk8BpnpyW8k0S9y8YSZCS0/5/f18dQhb0S1Wd6XNQnnV1x95g1mGbj9N2F5FjgPb=
yzsW5kXrk24GdTpYW52mS3q6Fn8ZlW4gfj9_7ZDJDpW5SzHzN3J46TdW2wsS8D28rybQVpPCMY1=
vXGmfN5xNGb8MHdG5W600WSC87tpBSW6Ktxf16KVxz2W7NLLbM37bMRSN34jMRgtHHFHW1rm42x=
5ctwg3W3SD6l05xN5n3W8W1CBJ71BdmqW2Q3G-M4LBtJKW1nC-wQ7X4mKRW2DjF_b5HrvLPW4z-=
C0_1pvxwQW7d9RT06hMpsfW7rm12s7PFD_WW5v2glf340W5xW3sdr401YkS58W51RCTQ8CqxHGW=
6Jf0Xx13R9qBW2YpRhq5l8RDmW7FHlmK2KxqzKW7N3_HC1myQvqW1gzzRn8jXFX4W7bRNTk6VWW=
MqVGjSMj5DcdSwW3KSJFd5V4VQPW6chrG24WgKhLW4PYR3c4lxgsHW2szJ_w7yf8S_W3fv-qL1F=
1XZfW3GvGyb8xCnj2W5WwtRT1vmlBXW84HD3q6J1GjwW4fb0K11TsP_8Vwz-fk8-TjByW34c3Rq=
4y2MF5V5rK_-5X36FYW8-X1yX2M48CkW3NFfwv2zwjZhW9lx4XK6pFhy2W918ZdQ7Zsqp1W4Hjt=
BL92W7ppW5RrPl16s066vW8Sgw139fG66NW62QdZc2jTMLCdCX_Hx03" target=3D"_blank" =
style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%" data-hs-=
link-id=3D"0"><img class=3D"hs-cta-img" id=3D"hs-cta-img-871f163e-d87b-46d4=
-93f9-4cc558f2636e" style=3D"vertical-align:bottom; -ms-interpolation-mode:=
bicubic; border-width:0px; width:auto !important; max-width:100% !important=
" mce_noresize=3D"1" src=3D"https://cta-image-cms2.hubspot.com/ctas/v2/publ=
ic/cs/il/?pg=3D871f163e-d87b-46d4-93f9-4cc558f2636e&amp;pid=3D364927&amp;ec=
id=3D&amp;hseid=3D2&amp;hsic=3Dtrue" alt=3D"Set up my password" width=3D"au=
to !important"></a></span></span><!-- end HubSpot Call-to-Action Code --></=
center>
<p style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-siz=
e-adjust:100%; line-height:1.5em"><span style=3D"background-color: transpar=
ent; color: inherit; font-size: inherit;">Once you have set up your passwor=
d, don=E2=80=99t forget to also bookmark the <span style=3D"color: #007fa5;=
"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*N9h2=
b5Rj8429N6By8nXR4rWy0/5/f18dQhb0S5fx8YXNsnW9kpyTt6ghkRDVWs4R057Y-gFW5r8vwP3=
mm42mVQzKZc63kYwJW8l8qPk8CZrTyW3Jswq68B-9dcN8zTLsQScn16W66jvd95y5jh-W1rfXlz=
1kRpb7W7Zs1mD25McgbW1SZkdK3fNkD_VQJJn63Lqnm_W8q5FTl6skKw2W64QZb68rvwMTW4D2p=
dC5RpkcdN3bqvN6RFkGqW6CGnLT7ldyjxW608MzT83xLwvW6xRLH_2jGw17W1hpX1G1Y8MRnW78=
mrrc71dpYHW691XBk23Xst2W2dJxR06JzKlNW1jqTck7NYmzsW6rFpvl7CVZSWW7XNr181Pd7wT=
W2bPXNG29WsK5W6gZ0bT1MfjMdW2cRJfg2fHZSbW3bwBFy5jg9t9W4PxC8j448hL1W2Fzzrh47Y=
k-YW3CBy4h27_9zCW8LFpRR7mt3V-W9f87tc78yW19W4Gnljl8FSrYjN76lgN9zDHfqW4CB_vV2=
k7PW6MtJCL43_BWgf22xrTg11" style=3D"-webkit-text-size-adjust:100%; -ms-text=
-size-adjust:100%; color:#007fa5" data-hs-link-id=3D"0" target=3D"_blank">l=
ogin page</a>.</span></span></p>
<p style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-siz=
e-adjust:100%; line-height:1.5em">&nbsp;</p>
<p style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-siz=
e-adjust:100%; line-height:1.5em"><em style=3D"background-color: transparen=
t; color: inherit; font-size: inherit;">Questions? Please reach out to our =
govBenchmark team by sending an email to <span style=3D"color: #007fa5;"><a=
 href=3D"mailto:govbenchmark@envisio.com" style=3D"-webkit-text-size-adjust=
:100%; -ms-text-size-adjust:100%; color:#007fa5" data-hs-link-id=3D"0" targ=
et=3D"_blank">govbenchmark@envisio.com</a></span>, or check out our <span s=
tyle=3D"color: #007fa5;"><a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5k=
vP7SVM5lLF96Hybr0/*V96B7T7H6NznW2pl4SB4ztmk40/5/f18dQhb0SbTK8XJ9l8W9kpyTt6g=
hkRDVWs4R057H86zW2KBfjg6mdtrJW8X0XFQ1WwpB4W31H3Ss55FSvFW8Wm1rS5m3NRqW634yBw=
50Cxs9W95S0C17dDWFFW63BbZ45v7rlKW3LrbbS964cbDW4shQzX50SXT6W61SSZm7mG7sDW51v=
X4y6H2lLvW2h82nD8hTJV4W8lwVXY8hS296N7bj1_4FCG-jW5rC5Q63l6qq3W7hNVWh2KFZxKW3=
-b1_01VJww0W7YkMQV97rB1bN8xq-GtSyV26W3hVmwk8nhsBlN2yqbYYYtSmbW97JYHj3MpP1BW=
4sw1VX39RyncW7bVBhM8w20s-MRbjvTZtR1MVdvgLs3NNTn3MXVfrCWysvmN2Bpw3XVPBKpW95_=
sR_3dGgwpVSrmQk5t0j-FW7wV0lT3Cdk0dW3wv7Sm6xZMY_W2Fq7mC7B_Kn-W1Vwr5J24DqNLV2=
15-p1Jp45tW2kP6y28601WcW3ZFlZm320pP8W977XPN2YX1NSW5SH_r96lRJl1w3xWwfqgcNd9c=
Xnf02" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; c=
olor:#007fa5" data-hs-link-id=3D"0" target=3D"_blank">FAQ page</a>.</span><=
/em></p></div>
</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"12" width=3D"100.0%" class=3D" column" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; width:100.0%; text-align:left; padding:0; fo=
nt-family:sans-serif; font-size:15px; line-height:1.5em; color:#696969" ali=
gn=3D"left">

<div class=3D"widget-span widget-type-text " style=3D"padding: 10px 0px 0px=
 0px; text-align: right" data-widget-type=3D"text">
<div class=3D"layout-widget-wrapper">
<div id=3D"hs_cos_wrapper_social_sharing_label" class=3D"hs_cos_wrapper hs_=
cos_wrapper_widget hs_cos_wrapper_type_text" style=3D"color: inherit; font-=
size: inherit; line-height: inherit;" data-hs-cos-general-type=3D"widget" d=
ata-hs-cos-type=3D"text"></div>
</div><!--end layout-widget-wrapper -->
</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"12" width=3D"100.0%" class=3D" column" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; width:100.0%; text-align:left; padding:0; fo=
nt-family:sans-serif; font-size:15px; line-height:1.5em; color:#696969" ali=
gn=3D"left">

<div class=3D"widget-span widget-type-social_sharing " style=3D"padding: 10=
px 0px 0px 0px; text-align: right" data-widget-type=3D"social_sharing">
<div class=3D"layout-widget-wrapper">
<div id=3D"hs_cos_wrapper_Social_Sharing" class=3D"hs_cos_wrapper hs_cos_wr=
apper_widget hs_cos_wrapper_type_social_sharing" style=3D"color: inherit; f=
ont-size: inherit; line-height: inherit;" data-hs-cos-general-type=3D"widge=
t" data-hs-cos-type=3D"social_sharing"><a href=3D"http://www.envisio.com/e1=
t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W8RR8TG1zyS7JW2hvP6y5BlcQ90/5/f18dQhb0S1Wd=
2frcn5M11YGL8PdrqN2nNK2KxvK0_W5CGj2L4B_Q1YVGPs818gsnGrW75yJJk5jV1T6W4mKqGH3=
hXgXbF5sGpgTcmpNV_v1Sh6wlbQCW1qb8s08wP8KKW8dZ1g59gGC6zW5wYpbz34dr0qW6mW1T75=
VtCBhW5GLgFz2fZVlvW4Z4yxv3ZM_hQW6lqn4h6160SCW29js6v6jNVRsW1J1H732JqQX3W7kVz=
Zt5_BHPDW5rpmRQ1rzYxGW17TwHV2fjMrrW3BHd2x3SJVJHW8x36z22PtSGBW3WQSTF7Q9y3MW2=
bFM6x8mdS_JW8KLBzM80HKzFW6bLJ1M4XKj1YW7kfvMF3GshWMW2XVzhN2bMDhKW6Nk1Z86Mj_P=
gW7RZ4p535RqDTW24BnDv8Sbmc2W2w125m31tCQhW290RTh1t237DW3WqFD21JvR0SW45HtH61t=
RW0zW1t2Lvm46DQDdW8wTYND7tbrrXW90yTHd7R6J7_W4BJhP047VY-4W5RwG8T1TQHbFW6tK-D=
B5qy1LFW3Pyqdl5057NtW94Q_Z27_YMWkW7MG_FP8GfhS8W5MBRn55ZVWKZW7zbRX116-XnYVZM=
wMZ1X5tLgW6H7wjF1LDmBPVR1P1Q650s8sW9jDsJj6q6KF6W9lXTNt7QQmP7W4Gk2Cp4m8VYJW4=
tCXHt4C6bD7W8Y-qKr5HhCKTN2Y6KHz628G4f2pRS5T03" target=3D"_blank" style=3D"-=
webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; width:24px; border=
-width:0px; border:0px" width=3D"24" data-hs-link-id=3D"0"><img src=3D"http=
s://static.hubspot.com/final/img/common/icons/social/facebook-24x24.png" cl=
ass=3D"hs-image-widget hs-image-social-sharing-24" style=3D"vertical-align:=
bottom; -ms-interpolation-mode:bicubic; max-height:24px; max-width:24px; bo=
rder-width:0px; border:0px" width=3D"24" hspace=3D"0" alt=3D"Share on Faceb=
ook"></a>&nbsp;<a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF=
96Hybr0/*W4kbW3K7L-nnKW25W2r52y4lSP0/5/f18dQhb0S1Wf7wzbK-M1x2YMryg_2W4VRlvN=
2Fbyy2W7m0yFR1Lg3PSW3VbgBm6KN5sNW3HYDL43_Tf9lW2P8FJD9k9Y00W5ntDvS5dqrs0W4nW=
Nr02ck4kyW8SfMgx5Gl-V3W4qDw1_9gTgHpW1CQNZN3YlPJGW39Z6WS710w_LVDmRq73fNgFlW5=
81wyQ1PqdnvW7K5TLJ5hGwjbW3Cg2DY530qCbW7NScyv2KK2cdW2bBnsF2HFm_7VcFyFX8DQMC9=
W1fcm6Q8rbj4fW7hBmrv2X-d22W51GZS4273BScW8xPWvt6f9hRbW2wsqL_11sTHvW1sJLVq5VK=
8DhW6k4h708vbrZQW2qCFLQ1S3LDPVrVcvt19WRnzW7r5t7w2J1zNYW60d1rW5pffygW4RX3sS2=
twN0LW5R_dkW69GFh1W1_Q1Qq4y4BKdN80M8SjYlpMwN8ktK21Fp-XXN6hSPn1Pv50mW203YH77=
wkQm7W3rNqB09gK7bjW4dKtGT33wCnrW77QFy42yPdN9W39xPNn6HX_5FW7FN0Zf8j-zbqN6Bn9=
l6VzgGsW9jP-Qh4zc4CFN6s9bQCnDPDsW9c2XdS8C6wSTW15Vqhl8fGdJVW27wJCQ169NQFVcRf=
Tc5Jx7pVW3BRP0R67mcdjN3bn7g02XVBsW1LsPGt9c1GDtW6zzCTc636BffW88tZjp9m0cXzW9b=
bjZc7W7kR6N6L58TScRTJMf95b1qZ11" target=3D"_blank" style=3D"-webkit-text-si=
ze-adjust:100%; -ms-text-size-adjust:100%; width:24px; border-width:0px; bo=
rder:0px" width=3D"24" data-hs-link-id=3D"0"><img src=3D"https://static.hub=
spot.com/final/img/common/icons/social/linkedin-24x24.png" class=3D"hs-imag=
e-widget hs-image-social-sharing-24" style=3D"vertical-align:bottom; -ms-in=
terpolation-mode:bicubic; max-height:24px; max-width:24px; border-width:0px=
; border:0px" width=3D"24" hspace=3D"0" alt=3D"Share on LinkedIn"></a>&nbsp=
;<a href=3D"http://www.envisio.com/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W2B6f=
W682r1wWW9jdZfM3HNMXM0/5/f18dQhb0S1Wd2WZmhFMWVPPHbBwyFW7D9V5B3gF0MkW60zxcr5=
nnLXJN36xzCkPSR6QW1L1c277m-_4jW1S2KFl4xBcBbW5QFfpL9ffMWNW2crMsy1MtTpGW4QN4L=
r7v-nyLW4Mk14h4Cm52BW99_Hjt5n2jfnW411bzh7v86XgVym5-M1Bn5SpW7rj-GD1cdN33W5r8=
MjB5gvgr2W5hynf34l9WL7V6ZLy61cWMCyW2HSJ-d5sgDFGVJnL0R3FxkvmW1f9gjF7nWTZbW4c=
gBfb14x_ZFVW2MTD3rH4WbW52YKt28FKyTWVZRJGV6pvxBvN5r9Dxm2vwRMW3BYddJ90_hkkW3S=
d_ZC1yzz5cW8Cfkdz8Z-38pW2nx6Sw3HkKhDW3lgZdV3XRBN-W7t2CPj6XpDLFW5BkjFY2JfJf9=
W6zxSqX2SFvn3W1dq5Jz8vnj8cW7kbBBc2_r4McW3NRJvw1K78bjN5gv6q1HbQVcW7xfPNF4hc7=
0qW4_1hdh1wHsrjW2_SfXn96DMQwW6qmyXn1CX9-PW4gsJKz27HZ1tW98vnRb8krjnFW1QvNnc9=
fRkDqW7fxmr86z3rHqN6v9qSpBnClrVYlGs47h_t42W6FkjjQ6scp8YW5SDssm8xpK5fW3jGSpZ=
3TBKN5W75dm1f6hjx8tW9533jB23Zn0MVm9pl63RcxgFF8cw9kZnx7ZW3DhmqB2Hgj6mW8tgX8X=
7Qlpk-W9f9JMd95f63fW6yNSzJ7L9ylLW5y927Q7SKH6PW9lxL9R4LmgRvVmf1Rg2tSK37121" =
target=3D"_blank" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adj=
ust:100%; width:24px; border-width:0px; border:0px" width=3D"24" data-hs-li=
nk-id=3D"0"><img src=3D"https://static.hubspot.com/final/img/common/icons/s=
ocial/twitter-24x24.png" class=3D"hs-image-widget hs-image-social-sharing-2=
4" style=3D"vertical-align:bottom; -ms-interpolation-mode:bicubic; max-heig=
ht:24px; max-width:24px; border-width:0px; border:0px" width=3D"24" hspace=
=3D"0" alt=3D"Share on Twitter"></a>&nbsp;<a href=3D"http://www.envisio.com=
/e1t/c/*W8gkbZs5kvP7SVM5lLF96Hybr0/*W15-MK739WYLpW2cqdN966pRND0/5/f18dQhb0S=
64C6X_sHCW11T3lS22ZSvMW7nkwQd60FCqFW8xYbWc8wF-5xW2p3ZnV8_vhKxW5ypK1x8rpp9mW=
1XkHsh4m5wg-W9cpjfr3ndK6MW7m5cny5v9bJXW72sL8t8dndybW2hfTTb7zN7_pW7QQ9bB3NGx=
z3W3b1n646wdlBbW79GPgj39786qW2rDTDn5ZNHFzN2LJf7j4HJWjW8BmlPX8vB8BJW3qb5fc5K=
7_xpW7D5d_w1bHx9wW4M25Xq3Sjs1kW5GC6Zk2lNWMfMl715Y5yg4yW8cppT-6HWCBYVTbQ2L5t=
-pJKW5kRBxB42W0TbW54kcKn3dXxXvN4XcPG-fG-wfV3t63F1bhCV0W1NcGvB35mpQ6N6LzqlYy=
rrq_W5DXvSZ1t-ZzVW5YKq6W4TQ8ZsW87-mbt6g49yTW5lvtnD4ZGHr8W3p_dP81qf8RTW6zVBs=
t1bj8swW3PBDFM95rS31W11CMyp8My-Y_W6JzfCw6jl7KqW5Nt1fj6l1DhjW9fSyMV8Thy8HW76=
gbCF3ZVCFFW8lRBsx899MtrW7zDh4P290XS5W5ZzgRB2gtPwyW810t4F5Lxm0yW70bdxC8hqKjS=
W3mlNf18_mrtkW3Nqjdb3Lhz8jN29L7slJXwJXW8trP5M4HywjlW9f6sSZ6q4Gm_W7QWkpf3sJk=
d-W2g2tls8Zc1F1W5s4j-S2xR8ynVgWk6G2p-M6R103" target=3D"_blank" style=3D"-we=
bkit-text-size-adjust:100%; -ms-text-size-adjust:100%; width:24px; border-w=
idth:0px; border:0px" width=3D"24" data-hs-link-id=3D"0"><img src=3D"https:=
//static.hubspot.com/final/img/common/icons/social/googleplus-24x24.png" cl=
ass=3D"hs-image-widget hs-image-social-sharing-24" style=3D"vertical-align:=
bottom; -ms-interpolation-mode:bicubic; max-height:24px; max-width:24px; bo=
rder-width:0px; border:0px" width=3D"24" hspace=3D"0" alt=3D"Share on Googl=
e+"></a></div>
</div><!--end layout-widget-wrapper -->
</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<!--end body wrapper -->
                                            </tbody></table>
                                        </td>
                                    </tr>
                                </tbody></table>
                                <!-- End Template Wrapper -->
                            </td>
                        </tr>
                        <tr>
                            <td align=3D"center" valign=3D"top" style=3D"-w=
ebkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0p=
t; mso-table-rspace:0pt">
                                <!-- Begin Template Footer -->
                                <div class=3D"footer-container-wrapper">
</div><table border=3D"0" cellpadding=3D"0" cellspacing=3D"0" width=3D"100%=
" id=3D"footerTable" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-=
adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:co=
llapse !important; background-color:#eeeeee; color:#007da5; font-family:san=
s-serif; font-size:12px; line-height:120%; padding-top:20px; padding-right:=
20px; padding-bottom:20px; padding-left:20px; text-align:center" bgcolor=3D=
"#eeeeee" align=3D"center">
                                    <tbody><tr>
<td align=3D"left" valign=3D"top" class=3D"bodyContent" width=3D"100%" cols=
pan=3D"12" style=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100=
%; mso-table-lspace:0pt; mso-table-rspace:0pt; color:#696969; font-family:s=
ans-serif; font-size:15px; line-height:150%; text-align:left">
<table cellpadding=3D"0" cellspacing=3D"0" border=3D"0" width=3D"100%" clas=
s=3D"templateColumnWrapper" style=3D"-webkit-text-size-adjust:100%; -ms-tex=
t-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:0pt; border-coll=
apse:collapse !important">
            <tbody><tr>
<td valign=3D"top" colspan=3D"12" width=3D"100.0%" class=3D" column" style=
=3D"-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lsp=
ace:0pt; mso-table-rspace:0pt; width:100.0%; text-align:left; padding:0; fo=
nt-family:sans-serif; font-size:15px; line-height:1.5em; color:#696969" ali=
gn=3D"left">

<div class=3D"widget-span widget-type-email_can_spam " style=3D"" data-widg=
et-type=3D"email_can_spam">
<p id=3D"footer" style=3D"margin-bottom: 1em; -webkit-text-size-adjust:100%=
; -ms-text-size-adjust:100%; font-family:Geneva, Verdana, Arial, Helvetica,=
 sans-serif; text-align:center; font-size:12px; line-height:1.34em; color:#=
007DA5; display:block" align=3D"center">
Envisio Solutions Inc.

&nbsp;&nbsp;250-13777 Commerce Parkway

&nbsp;

&nbsp;Richmond

&nbsp;British Columbia

&nbsp;&nbsp;V6V 2X3

&nbsp;&nbsp;Canada

<br><br>
You received this email because you are subscribed to Newsletter
 from Envisio Solutions Inc.
.
<br><br>
Update your <a class=3D"hubspot-mergetag" data-unsubscribe=3D"true" href=3D=
"http://www.envisio.com/hs/manage-preferences/unsubscribe-test?d=3DeyJlYSI6=
InBlcnNvbm1hbjJAZ21haWwuY29tIiwiZWMiOjIsInN1YnNjcmlwdGlvbklkIjoxLCJldCI6MTU=
xMTU0NDk0NDA5MywiZXUiOiI1NTg1NWZmMi05YTE4LTRhYmEtOWJmYi1hYzFjYmUxZGZjZmEifQ=
%3D%3D&amp;v=3D1&amp;_hsenc=3Dp2ANqtz-8W7qs9y9bofmAHfXd5Z2wh1LB0IjurzHlsSzq=
gj_qmp-fharj553sqqPxqOkXsF3X1T17weYgkP0CjrN9nT8U7kqQGVw&amp;_hsmi=3D2" styl=
e=3D"-ms-text-size-adjust:100%; -webkit-text-size-adjust:none; font-weight:=
normal; text-decoration:underline; whitespace:nowrap; color:#007DA5" data-h=
s-link-id=3D"0" target=3D"_blank">email preferences</a>
 to choose the types of emails you receive.
<br><br>
&nbsp;<a class=3D"hubspot-mergetag" data-unsubscribe=3D"true" href=3D"http:=
//www.envisio.com/hs/manage-preferences/unsubscribe-all-test?d=3DeyJlYSI6In=
BlcnNvbm1hbjJAZ21haWwuY29tIiwiZWMiOjIsInN1YnNjcmlwdGlvbklkIjoxLCJldCI6MTUxM=
TU0NDk0NDA5MywiZXUiOiI1NTg1NWZmMi05YTE4LTRhYmEtOWJmYi1hYzFjYmUxZGZjZmEifQ%3=
D%3D&amp;v=3D1&amp;_hsenc=3Dp2ANqtz-8W7qs9y9bofmAHfXd5Z2wh1LB0IjurzHlsSzqgj=
_qmp-fharj553sqqPxqOkXsF3X1T17weYgkP0CjrN9nT8U7kqQGVw&amp;_hsmi=3D2" style=
=3D"-ms-text-size-adjust:100%; -webkit-text-size-adjust:none; font-weight:n=
ormal; text-decoration:underline; whitespace:nowrap; color:#007DA5" data-hs=
-link-id=3D"0" target=3D"_blank">Unsubscribe from all future emails</a>
&nbsp;
</p>

</div><!--end widget-span -->
   </td>
           </tr>
    </tbody></table>
   </td>
</tr>
<!--end footer wrapper -->
                                    <tr>
                                        <td style=3D"-webkit-text-size-adju=
st:100%; -ms-text-size-adjust:100%; mso-table-lspace:0pt; mso-table-rspace:=
0pt"></td>
                                    </tr>
                                </tbody></table>
                                <!-- End Template Footer -->
                            </td>
                        </tr>
                    </tbody></table>
                    <!-- End Template Container -->
                </td>
            </tr>
        </tbody></table>
   =20
<!-- end coded_template: id:5437764569 path:generated_layouts/5437647422.ht=
ml -->
<img src=3D"http://www.envisio.com/e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*Vjdr=
sD2VFythW3QJLQB6jd8BJ0/5/f18dQhb0KdhFBRTRVW5kgQqJ25yxJ6N2zQNWY3k99nVslmXx56=
tc0yW6VzwTx1f6YlXW7dDt2k51fkyHW8y8x9Z25qfkVW6Ngtjg49FShzW6_S05N46r0CSW89Hny=
H6TfmrzW6t7f6J2trFNkW3tYy172WBfhwTZMjL8lzTs8103" alt=3D"" width=3D"1" heigh=
t=3D"1" border=3D"0" style=3D"display:none!important;min-height:1px!importa=
nt;width:1px!important;border-width:0!important;margin-top:0!important;marg=
in-bottom:0!important;margin-right:0!important;margin-left:0!important;padd=
ing-top:0!important;padding-bottom:0!important;padding-right:0!important;pa=
dding-left:0!important"><style>@media print{#_hs { background-image: url(\'h=
ttp://www.envisio.com/e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*VwkYgs1qMhx7W34TQ=
-l20mSDD0/5/f18dQhb0KdhCBWbDXW5kgQqJ25yxJ6W2zQ_332pttPtN2zcnvsxJ-7bV3qXTz3m=
PqZhW2yH57w6P4hQsVyqXXV5CkwTkW96L4_r30Hpx6W12K_T69l0dZ7N8_71h65pbmdW6Wjmw18=
ZY0h8W4mxGDc8R6JT9V1-YZq9b3pS6102\');}} div.OutlookMessageHeader {background=
-image:url(\'http://www.envisio.com/e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*W86t=
cl592fVMCW8fjVjc2RJz520/5/f18dQhb0KdhBBXTxrW5kgQqJ25yxJ6W2zR4Kv9hHbxgW2nCNK=
437P0gsW15hnZl2-dVvPVc99T596dK3VW4rhgs96P4lCXW4vgKM119TkvlW3nNLLk4DBJGFW6RX=
GpS7ZdRYhW640Qzj8VcrbZW5VQG432lg5-ZW3lyq6R68lXBxf3trWRC04\')} table.moz-emai=
l-headers-table {background-image:url(\'http://www.envisio.com/e1t/o/*W48Cql=
K1hYLf4W4xLLcK7dQWQL0/*W86tcl592fVMCW8fjVjc2RJz520/5/f18dQhb0KdhBBXTxrW5kgQ=
qJ25yxJ6W2zR4Kv9hHbxgW2nCNK437P0gsW15hnZl2-dVvPVc99T596dK3VW4rhgs96P4lCXW4v=
gKM119TkvlW3nNLLk4DBJGFW6RXGpS7ZdRYhW640Qzj8VcrbZW5VQG432lg5-ZW3lyq6R68lXBx=
f3trWRC04\')} blockquote #_hs {background-image:url(\'http://www.envisio.com/=
e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*W86tcl592fVMCW8fjVjc2RJz520/5/f18dQhb0K=
dhBBXTxrW5kgQqJ25yxJ6W2zR4Kv9hHbxgW2nCNK437P0gsW15hnZl2-dVvPVc99T596dK3VW4r=
hgs96P4lCXW4vgKM119TkvlW3nNLLk4DBJGFW6RXGpS7ZdRYhW640Qzj8VcrbZW5VQG432lg5-Z=
W3lyq6R68lXBxf3trWRC04\')} #MailContainerBody #_hs {background-image:url(\'ht=
tp://www.envisio.com/e1t/o/*W48CqlK1hYLf4W4xLLcK7dQWQL0/*W86tcl592fVMCW8fjV=
jc2RJz520/5/f18dQhb0KdhBBXTxrW5kgQqJ25yxJ6W2zR4Kv9hHbxgW2nCNK437P0gsW15hnZl=
2-dVvPVc99T596dK3VW4rhgs96P4lCXW4vgKM119TkvlW3nNLLk4DBJGFW6RXGpS7ZdRYhW640Q=
zj8VcrbZW5VQG432lg5-ZW3lyq6R68lXBxf3trWRC04\')}</style><div id=3D"_hs"></div=
></body></html>';
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
