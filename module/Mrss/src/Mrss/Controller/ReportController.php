<?php

namespace Mrss\Controller;

use DoctrineORMModule\Proxy\__CG__\Mrss\Entity\Benchmark;
use Mrss\Entity\Chart;
use Mrss\Form\Explore;
use Mrss\Form\PeerComparisonDemographics;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Service\Report;
use Mrss\Form\PeerComparison;
use Mrss\Entity\PeerGroup;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Formatter\Simple;

class ReportController extends AbstractActionController
{
    /**
     * @var Report
     */
    protected $reportService;

    protected $sessionContainer;

    protected $observations;

    /**
     * @return Report\Percentile
     */
    protected function getPercentileService()
    {
        $percentileService = $this->getServiceLocator()->get('service.report.percentile');

        return $percentileService;
    }

    public function calculateAction()
    {
        $this->longRunningScript();
        $start = microtime(true);

        $percentileService = $this->getPercentileService();

        $years = $percentileService->getCalculationInfo();
        $yearToPrepare = $this->params()->fromRoute('year');

        /*if (!empty($yearToPrepare)) {
            // Now calculate percentiles
            //$stats = $this->getReportService()->calculateForYear($yearToPrepare);
            $stats = $percentileService->calculateForYear($yearToPrepare);

            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];
            $noData = $stats['noData'];
            $compute = round($stats['computeElapsed'], 1);

            $elapsed = round(microtime(true) - $start, 1);

            $this->flashMessenger()->addSuccessMessage(
                "Report prepared for $yearToPrepare. Benchmarks: $benchmarks. Percentiles: $percentiles.
                Percentile ranks: $percentileRanks. Benchmarks without data: $noData.
                Total elapsed time: $elapsed seconds. Time on computed benchmarks: $compute seconds."
            );

            return $this->redirect()->toRoute('reports/calculate');
        }*/

        // Get observation ids
        $observationIds = array();
        $benchmarkIds = array();
        foreach ($years as $year => $yearInfo) {
            $yearIds = array();
            $subs = $this->getSubscriptionModel()->findWithPartialObservations(
                $this->currentStudy(),
                $year,
                array(),
                false,
                true
            );

            foreach ($subs as $sub) {
                $yearIds[] = $sub->getObservation()->getId();
            }

            $observationIds[$year] = $yearIds;


            // Get the Ids of benchmarks on the report
            foreach ($this->getBenchmarkModel()->findOnReport() as $benchmark) {
                $benchmarkIds[$year][] = $benchmark->getId();
            }

        }

        // Get System ids
        $currentYear = $this->currentStudy()->getCurrentYear();
        $systemIds = array();
        //foreach ($this->getSystemModel()->findAll() as $system) {
        foreach ($this->getSystemModel()->findWithSubscription($currentYear, $this->currentStudy()->getId()) as $system) {
            $systemIds[] = $system->getId();
        }





        return array(
            'years' => $years,
            'study' => $this->currentStudy(),
            'observationIds' => $observationIds,
            'systemIds' => $systemIds,
            'benchmarkIds' => $benchmarkIds
        );
    }

    public function debug($message, $var)
    {
        $seconds = microtime(true) - REQUEST_MICROTIME;

        echo "$seconds since request started.<h3>$message</h3>";

        if ($var) {
            pr($var);
        }

    }

    /**
     * Calculate national report percentiles and ranks for a single benchmark/year
     */
    public function calculateOneAction()
    {
        $this->debug("Start", null);

        $benchmarkId = $this->params()->fromRoute('benchmark');
        $year = $this->params()->fromRoute('year');
        $position = $this->params()->fromRoute('position');

        $percentileService = $this->getPercentileService();
        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

        // If this is the first benchmark, clear existing percentiles
        if ($position == 'first') {
            $percentileService->clearPercentiles($year);
        }

        // Last?
        if ($position == 'last') {
            $settingKey = $this->getReportService()->getReportCalculatedSettingKey($year);
            $this->getReportService()->getSettingModel()->setValueForIdentifier($settingKey, date('c'));
        }


        $this->debug("About to calculate", $benchmarkId);

        // Now actually calculate and save percentiles
        $percentileService->calculateForBenchmark($benchmark, $year);

        $this->debug("Preflush", null);

        // Flush
        $percentileService->getPercentileModel()->getEntityManager()->flush();


        $this->debug("Postflush", null);

        $view = new JsonModel(
            array(
                'status' => 'ok',
                'benchmark' => $benchmark
            )
        );

        return $view;
    }

    public function computeAction()
    {
        $yearToPrepare = $this->params()->fromRoute('year');

        if (!empty($yearToPrepare)) {
            $this->longRunningScript();
            $start = microtime(true);

            $percentileService = $this->getPercentileService();
            $percentileService->calculateAllComputedFields($yearToPrepare);
            $elapsed = round(microtime(true) - $start);

            $this->flashMessenger()
                ->addSuccessMessage("Benchmarks calculated for $yearToPrepare. It took $elapsed seconds.");

            return $this->redirect()->toRoute('reports/calculate');
        }
    }

    public function computeOneAction()
    {
        takeYourTime();

        $observationId = $this->params()->fromRoute('observation');
        $debug = $this->params()->fromRoute('debug');
        $debugColumn = $this->params()->fromRoute('benchmark');

        $status = 'ok';

        $observation = $this->getObservationModel()->find($observationId);


        if ($observation) {
            $service = $this->getPercentileService()->getComputedFieldsService();
            $service->setDebug($debug);
            $service->setDebugDbColumn($debugColumn);

            try {
                $service->calculateAllForObservation($observation);
            } catch (\Exception $e) {
                $message = $e->getMessage();
                //echo "! " . $message;
                $status = $message;

            }
        } else {
            $status = '404';
        }



        $view = new JsonModel(
            array(
                'status' => $status,
                'observation' => $observationId
            )
        );

        return $view;
    }

    public function calculateChangesAction()
    {
        $status = 'ok';
        $observationId = $this->params()->fromRoute('observation');

        $changesService = $this->getPercentChangeService();
        $changesService->calculateChanges($observationId);

        //prd(get_class($changesService));

        $view = new JsonModel(
            array(
                'status' => $status,
                'observation' => $observationId
            )
        );

        return $view;
    }

    /**
     * @return \Mrss\Service\Report\Changes
     */
    protected function getPercentChangeService()
    {
        return $this->getServiceLocator()->get('service.report.changes');
    }

    public function percentChangesAction()
    {
        takeYourTime();
        $format = $this->params()->fromRoute('format');

        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        /** @var \Mrss\Model\PercentChange $percentChangeModel */
        $percentChangeModel = $this->getServiceLocator()->get('model.percentchange');
        $changes = $percentChangeModel->findByYear($year);

        if ($format == 'excel') {
            $this->getPercentChangeService()->download($changes);
            die;
        }

        return array(
            'changes' => $changes
        );
    }

    /**
     * @return \Mrss\Model\System
     */
    protected function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
    }

    public function calculateOneSystemAction()
    {
        takeYourTime();
        $start = microtime(true);

        $systemId = $this->params()->fromRoute('system');
        $system = $this->getSystemModel()->find($systemId);
        if (empty($system)) {
            throw new \Exception(
                'Valid system id required to calculate system reports.'
            );
        }

        $yearToPrepare = $this->params()->fromRoute('year');
        if (empty($yearToPrepare)) {
            throw new \Exception(
                'Year parameter required to calculate system reports.'
            );
        }

        $benchmarkId = $this->params()->fromRoute('benchmark');
        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);
        if (empty($benchmark)) {
            throw new \Exception(
                'Year parameter required to calculate system reports.'
            );
        }


        $position = $this->params()->fromRoute('position');

        if ($position == 'first') {
            $this->getPercentileService()->clearPercentiles($yearToPrepare, $system);
        } elseif ($position == 'last') {
            $this->getPercentileService()->updateCalculationDate($yearToPrepare, $system);
        }

        $this->getPercentileService()->calculateForBenchmark($benchmark, $yearToPrepare, $system);

        $this->getPercentileService()->getPercentileModel()->getEntityManager()->flush();


        $elapsed = microtime(true) - $start;

        $view = new JsonModel(
            array(
                'status' => 'ok',
                'elapsed' => $elapsed
            )
        );

        return $view;

    }

    public function calculateSystemsAction()
    {
        $this->longRunningScript();
        $start = microtime(true);

        $yearToPrepare = $this->params()->fromRoute('year');

        if (empty($yearToPrepare)) {
            throw new \Exception(
                'Year parameter required to calculate system reports.'
            );
        }



        if (!empty($yearToPrepare)) {
            // Now calculate percentiles
            $stats = $this->getPercentileService()
                ->calculateSystems($yearToPrepare);
            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];
            $noData = $stats['noData'];
            $systems = $stats['systems'];

            $elapsed = round(microtime(true) - $start, 1);

            $this->flashMessenger()->addSuccessMessage(
                "Reports prepared for $systems systems for $yearToPrepare. Benchmarks: $benchmarks.
                Percentiles: $percentiles.
                Percentile ranks: $percentileRanks. Benchmarks without data: $noData.
                Elapsed time: $elapsed seconds."
            );

            return $this->redirect()->toRoute('reports/calculate');
        }
    }

    /**
     * @return \Mrss\Service\Report\Outliers
     */
    protected function getOutlierService()
    {
        return $this->getServiceLocator()->get('service.report.outliers');
    }

    public function calculateOutliersAction()
    {
        $this->longRunningScript();

        $yearToPrepare = $this->params()->fromRoute('year');

        $stats = $this->getOutlierService()
            ->calculateOutliersForYear($yearToPrepare);
        $low = $stats['low'];
        $high = $stats['high'];
        $missing = $stats['missing'];
        $time = $stats['time'];
        $total = $low + $high + $missing;

        $this->flashMessenger()->addSuccessMessage(
            "$total outliers calculated. Low: $low. High: $high. Missing: $missing.
            Time to calculate: $time."
        );

        return $this->redirect()->toRoute('reports/calculate');
    }

    public function calculateOutlierAction()
    {
        takeYourTime();

        $benchmarkId = $this->params()->fromRoute('benchmark');
        $year = $this->params()->fromRoute('year');
        $position = $this->params()->fromRoute('clear');

        // First?
        if ($position == 'first') {
            $this->getOutlierService()->clearOutliers($year);
        }

        // Calculate them
        if ($benchmark = $this->getBenchmarkModel()->find($benchmarkId)) {
            $this->getOutlierService()->calculateOutlier($benchmark, $year);
        }

        // Last?
        if ($position == 'last') {
            $this->getOutlierService()->saveReportCalculationDate($year);
        }

        $view = new JsonModel(
            array(
                'status' => 'ok',
                'benchmark' => $benchmarkId
            )
        );

        return $view;
    }

    public function adminOutliersAction()
    {
        takeYourTime();

        $collegeId = $this->params()->fromRoute('college_id');
        $outlierReport = $this->getServiceLocator()->get('service.report.outliers')
            ->getAdminOutlierReport($collegeId);

        return array(
            'report' => $outlierReport,
            'showDetails' => !empty($collegeId)
        );
    }

    public function outlierAction()
    {
        $college = $this->currentCollege();
        $outlierReport = $this->getServiceLocator()->get('service.report.outliers')
            ->getOutlierReport($college);

        return array(
            'report' => $outlierReport,
            'studyName' => $this->currentStudy()->getName(),
            'year' => $this->currentStudy()->getCurrentYear(),
            'showDetails' => true
        );
    }

    public function emailOutliersAction()
    {
        $this->longRunningScript();

        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');

        /** @var \Mrss\Service\Report\Outliers $outliersService */
        $outliersService = $this->getServiceLocator()->get('service.report.outliers');

        $task = $this->params()->fromRoute('task');
        if ($task == 'preview') {
            $stats = $outliersService->emailOutliers($renderer, false);
        } elseif ($task == 'send') {
            $stats = $outliersService->emailOutliers($renderer);

            $count = $stats['emails'];
            $this->flashMessenger()->addSuccessMessage("$count outlier emails sent.");
            return $this->redirect()->toRoute('reports/calculate');
        }

        return array(
            'preview' => $stats['preview']
        );

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

    public function nationalAction()
    {
        // HTML or Excel?
        $format = $this->params()->fromRoute('format');

        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        if ($this->currentStudy()->getId() == 2 && $format != 'excel') {
            return $this->maxNationalAction();
        }

        // Is this a system report?
        $systemVersion = $this->params()->fromRoute('system');
        $system = null;
        if ($systemVersion) {
            // Confirm they're actually part of a system
            $system = $this->currentCollege()->getSystem();

            if (empty($system)) {
                $this->flashMessenger()->addErrorMessage(
                    'Your institution is not part of a system.'
                );

                return $this->redirect()->toUrl('/members');
            }
        }

        $year = $this->getYearFromRouteOrStudy();
        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->currentStudy());

        $subscription = $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege(), $this->currentStudy());

        if (empty($subscription)) {
            //throw new \Exception('Subscription not found for year ' . $year);

            return $this->observationNotFound();
        }

        // Nccbp migration: temporary
        if (false && $year < 2014 && !$this->isAllowed('adminMenu', 'view')) {
            $this->flashMessenger()->addErrorMessage(
                "Reports prior to 2014 are under review and will be available soon."
            );
            return $this->redirect()->toUrl('/reports/national/2014');
        }

        /** @var \Mrss\Service\Report\National $reportService */
        $reportService = $this->getServiceLocator()->get('service.report.national');
        $reportData = $reportService->getData($subscription, $system);


        // Download?
        if ($format == 'excel') {
            $this->getServiceLocator()->get('service.report.national')
                ->download($reportData, $system);
            die;
        }

        $reportPath = 'national';
        if ($system) {
            $reportPath = 'system';
        }

        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'reportData' => $reportData,
            'college' => $subscription->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels(),
            'system' => $system,
            'reportPath' => $reportPath
        );
    }

    public function summaryAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $year = $this->getYearFromRouteOrStudy();
        $print = $this->params()->fromRoute('print');
        $printMedia = 'print';
        if ($print) {
            $printMedia .= ', screen';
        }

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()->getSummaryReportData($observation);

        return array(
            'reportData' => $reportData,
            'printMedia' => $printMedia,
            'year' => $year,
            'print' => $print,
            'college' => $observation->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels()
        );
    }

    public function executiveprintAction()
    {
        return $this->executiveAction();
    }

    public function executiveAction()
    {
        $open = true;

        if ($this->params()->fromRoute('open')) {
            $open = true;
        }
        $ipeds = $this->params()->fromRoute('ipeds');
        if ($ipeds) {
            /** @var \Mrss\Model\college $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $college = $collegeModel->findOneByIpeds($ipeds);
        }

        if (empty($college)) {
            $college = $this->currentCollege();
        }


        $year = $this->getYearFromRouteOrStudy($college);

        $subscriptions = $college->getSubscriptionsForStudy($this->currentStudy());
        // Don't show 2015 executive report yet
        $newSubs = array();
        $yearToSkip = 2016;
        foreach ($subscriptions as $subscription) {
            if ($subscription->getYear() != $yearToSkip) {
                $newSubs[] = $subscription;
            }
        }
        $subscriptions = $newSubs;
        if ($year == $yearToSkip) {
            $year = $year - 1;
        }



        //$this->view->headTitle('test');

        // Nccbp migration: temporary
        /*if ($year < 2014 && !$this->isAllowed('adminMenu', 'view')) {
            $this->flashMessenger()->addErrorMessage(
                "Reports prior to 2014 are under review and will be available soon."
            );
            return $this->redirect()->toUrl('/reports/executive/2014');
        }*/


        /** @var \Mrss\Entity\Observation $observation */
        $observation = $college->getObservationForYear($year);

        if (empty($observation)) {
            return $this->observationNotFound();
        }

        $executive = $this->getServiceLocator()->get('service.report.executive');
        $executive->setObservation($observation);
        $reportData = $executive->getData();

        $forcePrintStyles = $this->params()->fromRoute('print');
        if ($forcePrintStyles) {
            $media = 'screen,print';
        } else {
            $media = 'print';
        }

        $view = new ViewModel(
            array(
                'reportData' => $reportData,
                'year' => $year,
                'subscriptions' => $subscriptions,
                'college' => $college,
                'open' => $open,
                'media' => $media
            )
        );
        $view->setTemplate('mrss/report/executive.phtml');

        return $view;
    }

    public function executiveListAction()
    {
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

        $ipedsIds = array();
        foreach ($subscriptions as $subscription) {
            $ipeds = $subscription->getCollege()->getIpeds();
            $ipedsIds[] = $ipeds;
        }

        return array(
            'ipedsIds' => $ipedsIds
        );
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
            /** @var \Mrss\Model\Subscription $subModel */
            $subModel = $this->getServiceLocator()->get('model.subscription');

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

    public function peerAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        // This was causing errors, namely the year not being saved with the peer group. No idea why,
        // but the replacement code below works better.
        //$years = $this->currentCollege()->getYearsWithSubscriptions($this->currentStudy());

        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $subs = $subscriptionModel
            ->findByCollegeAndStudy($this->currentCollege()->getId(), $study->getId());
        $years = array();
        foreach ($subs as $sub) {
            if (!$study->getReportsOpen() && $sub->getYear() == $study->getCurrentYear()) {
                continue;
            }
            $years[] = $sub->getYear();
        }
        rsort($years);

        //$s = microtime(1);
        $defaultBenchmarks = $this->getPeerBenchmarks($years[0], true);
        //$e = microtime(1) - $s;
        //prd($e);

        $form = new PeerComparison(
            $years,
            $defaultBenchmarks
        );

        $peerGroup = $this->getPeerGroupFromSession();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $peerGroup->setYear($data['reportingPeriod']);
                //$peerGroup->setBenchmarks($data['benchmarks']);
                $this->getSessionContainer()->benchmarks = $data['benchmarks'];
                $this->getSessionContainer()->peers = $data['peers'];
                $this->getSessionContainer()->year = $data['reportingPeriod'];
                $this->getSessionContainer()->peerGroupName = null;
                $peerGroup->setPeers($data['peers']);

                // Save to db?
                if ($name = $data['name']) {
                    $peerGroup->setName($name);

                    $currentUser = $this->zfcUserAuthentication()->getIdentity();
                    $peerGroup->setUser($currentUser);


                    // See if it exists
                    $existingGroup = $this->getPeerGroupModel()
                        ->findOneByUserAndName($currentUser, $name);

                    if ($existingGroup) {
                        // Don't modify existing groups
                        //$peerGroup->setId($existingGroup->getId());
                        //$peerGroup->setStudy($this->currentStudy());
                        //$this->getPeerGroupModel()->getEntityManager()
                        //    ->merge($peerGroup);
                    } else {
                        //$groupToSave = clone $peerGroup;
                        $peerGroup->setStudy($this->currentStudy());
                        $this->getPeerGroupModel()->save($peerGroup);

                        $this->getSessionContainer()->peerGroupName = $peerGroup->getName();

                        $this->getPeerGroupModel()->getEntityManager()->flush();

                        $this->flashMessenger()->addSuccessMessage(
                            "The peer group $name has been saved."
                        );
                    }

                }

                //$this->savePeerGroupToSession($peerGroup);

                return $this->redirect()->toRoute('reports/peer-results');
            }
        }

        // Prepare saved peer groups for javascript
        $peerGroups = array();
        $user = $this->zfcUserAuthentication()->getIdentity();
        $groups = $this->getPeerGroupModel()->findByUserAndStudy($user, $this->currentStudy());
        foreach ($groups as $group) {
            $peerGroups[] = array(
                'name' => $group->getName(),
                'id' => $group->getId(),
                'peers' => $group->getPeers()
            );
        }
        $peerGroups = json_encode($peerGroups);

        $criteria = $this->getCriteriaFromSession();
        $criteria = $this->addCriteriaLabels($criteria);

        return array(
            'form' => $form,
            'peerGroup' => $peerGroup,
            'peerGroups' => $peerGroups,
            'criteria' => $criteria
        );
    }

    protected function addCriteriaLabels($criteria)
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $allCriteria = $study->getCriteria();

        $withLabels = array();
        foreach ($criteria as $dbColumn => $value) {
            // We only need labels for those with a value
            if (!empty($value)) {
                // Implode arrays
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                if ($dbColumn == 'states') {
                    $withLabels['States'] = $value;
                    continue;
                }

                foreach ($allCriteria as $criterion) {
                    if ($dbColumn == $criterion->getBenchmark()->getDbColumn()) {
                        $withLabels[$criterion->getName()] = $value;
                        continue;
                    }
                }
            }
        }

        return $withLabels;
    }

    public function peerResultsAction()
    {
        takeYourTime();

        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        /** @var \Mrss\Service\Report\Peer $peerService */
        $peerService = $this->getServiceLocator()->get('service.report.peer');

        $format = $this->params()->fromRoute('format');

        $benchmarks = $this->getSessionContainer()->benchmarks;
        $peers = $this->getSessionContainer()->peers;
        $year = $this->getSessionContainer()->year;

        $peerGroupName = $this->getSessionContainer()->peerGroupName;
        $peerService->setShowPeerDataYouDidNotSubmit($this->getStudyConfig()->show_peer_data_you_did_not_submit);

        $report = $peerService->getPeerReport($benchmarks, $peers, $this->currentCollege(), $year, $peerGroupName);

        if ($format == 'excel') {
            $peerService->downloadPeerReport($report);
        }

        return array(
            'peerGroupName' => $peerGroupName,
            'report' => $report
        );
    }

    public function deletePeerGroupAction()
    {
        $id = $this->params()->fromPost('peerGroup');

        $group = $this->getPeerGroupModel()->find($id);

        // Make sure the group belongs to the current college
        if (!empty($group) &&
            $group->getCollege()->getId() == $this->currentCollege()->getId()) {
            $this->getPeerGroupModel()->delete($group);
        } else {
            echo 'Cannot delete peer group ' . $id;
        }

        die('ok');
    }

    public function peerdemographicAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $form = new PeerComparisonDemographics($this->currentStudy());

        $criteria = $this->getCriteriaFromSession();
        $form->setData($criteria);

        /*$peerGroup = $this->getPeerGroupFromSession();

        $em = $this->getServiceLocator()->get('em');

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\PeerGroup'));
        $form->bind($peerGroup);*/

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            unset($postData['buttons']);

            // Handle empty multiselects
            /*$multiselects = array(
                'states',
                'environments',
                'facultyUnionized',
                'staffUnionized',
                'institutionalType',
                'institutionalControl',
                'onCampusHousing',
                'fourYearDegrees'
            );

            foreach ($multiselects as $multiselect) {
                if (empty($postData[$multiselect])) {
                    $postData[$multiselect] = array();
                }
            }*/

            $form->setData($postData);

            if ($form->isValid()) {
                $this->saveCriteriaToSession($postData);

                return $this->redirect()->toRoute('reports/peer');
            }
        }

        return array(
            'form' => $form
        );
    }

    protected function saveChart($config)
    {
        $chartModel = $this->getChartModel();
        $study = $this->currentStudy();
        $college = $this->currentCollege();
        $name = $config['title'];
        $type = $config['presentation'];
        $description = $config['content'];


        // First, see if we're updating a chart with the same name
        $chart = $chartModel->findByStudyCollegeAndName($study, $college, $name);

        // If not, create a chart entity
        if (empty($chart)) {
            $chart = new Chart();
            $chart->setStudy($study);
            $chart->setCollege($college);
            $chart->setName($name);
        }

        // Apply the updates
        $chart->setType($type);
        $chart->setConfig($config);
        $chart->setDescription($description);

        // Save it and flush
        $chartModel->save($chart);
        $chartModel->getEntityManager()->flush();
    }

    /**
     * @return \Mrss\Model\Chart
     */
    protected function getChartModel()
    {
        return $this->getServiceLocator()->get('model.chart');
    }


    public function bestPerformersAction()
    {
        $report = $this->getServiceLocator()->get('service.report.performers');
        $year = $this->getYearFromRouteOrStudy();

        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->currentStudy());

        $subscription = $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege(), $this->currentStudy());
        if (empty($subscription)) {
            //throw new \Exception('Subscription not found for year ' . $year);

            return $this->observationNotFound();
        }


        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'reportData' => $report->getBenchmarks($year)
        );
    }

    public function strengthsAction()
    {
        $threshold = 75;

        /** @var \Mrss\Service\Report\Executive $report */
        $report = $this->getServiceLocator()->get('service.report.executive');
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentCollege()->getObservationForYear($year);
        $report->setObservation($observation);


        $strengths = $report->getStrengths(false, $threshold);
        $weaknesses = $report->getWeaknesses($threshold);

        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->currentStudy());

        $subscription = $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege(), $this->currentStudy());

        if (empty($subscription)) {
            return $this->observationNotFound();
        }


        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses
        );

    }

    public function bestPerformersResultAction()
    {
        $report = $this->getServiceLocator()->get('service.report.performers');
        $year = $this->params()->fromRoute('year');
        $benchmarkId = $this->params()->fromRoute('benchmark');

        $colleges = $report->getBestPerformers($year, $benchmarkId);

        $view = new JsonModel(
            array(
                'colleges' => implode('<br>', $colleges)
            )
        );

        return $view;
    }

    public function institutionalAction()
    {
        return array(
            'college' => $this->currentCollege(),
        );
    }

    public function allInstitutionalAction()
    {

        $viewRender = $this->getServiceLocator()->get('ViewRenderer');

        $reports = '';
        $reports .= $viewRender->render(
            'mrss/report/institution-costs.phtml',
            $this->institutionCostsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/instructional-costs.phtml',
            $this->instructionalCostsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/instructional-activity-costs.phtml',
            $this->instructionalActivityCostsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/unit-costs.phtml',
            $this->unitCostsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/unit-demographics.phtml',
            $this->unitDemographicsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/student-services-costs.phtml',
            $this->studentServicesCostsAction()
        );

        $reports .= $viewRender->render(
            'mrss/report/academic-support.phtml',
            $this->academicSupportAction()
        );


        return array(
            'reports' => $reports,
            'college' => $this->currentCollege(),
        );
    }

    public function institutionCostsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        $charts = $report->getInstitutionCosts($observation);

        return array(
            'college' => $this->currentCollege(),
            'observation' => $observation,
            'charts' => $charts
        );
    }

    public function instructionalCostsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData, $chart) = $report->getInstructionalCosts($observation);

        return array(
            'college' => $this->currentCollege(),
            'observation' => $observation,
            'reportData' => $reportData,
            'chart' => $chart,
            'fields' => $report->getInstructionalCostFields()
        );
    }

    public function instructionalActivityCostsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData, $charts) = $report->getInstructionalActivityCosts($observation);

        return array(
            'reportData' => $reportData,
            'charts' => $charts,
            'headings' => $report->getActivities()
        );
    }

    public function unitCostsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData/*, $charts*/) = $report->getUnitCosts($observation);

        return array(
            'reportData' => $reportData,
            //'charts' => $charts
        );
    }

    public function unitDemographicsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData, $charts) = $report->getUnitDemographics($observation);

        return array(
            'reportData' => $reportData,
            'charts' => $charts,
            'headings' => $report->getUnitDemographicsFields()
        );
    }

    public function studentServicesCostsAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData, $charts) = $report->getStudentServicesCosts($observation);

        return array(
            'reportData' => $reportData,
            'charts' => $charts,
            'headings' => $report->getUnitDemographicsFields()
        );
    }

    public function academicSupportAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);

        /** @var \Mrss\Service\Report\Max\Internal $report */
        $report = $this->getServiceLocator()->get('service.report.max.internal');
        list($reportData, $charts) = $report->getAcademicSupport($observation);

        return array(
            'reportData' => $reportData,
            'charts' => $charts,
            'headings' => $report->getUnitDemographicsFields()
        );
    }

    public function maxNationalAction()
    {
        /** @var \Mrss\Service\Report\Max\National $report */
        $report = $this->getServiceLocator()->get('service.report.max.national');

        $year = $this->getYearFromRouteOrStudy();

        $collegeId = $this->currentCollege()->getId();
        $studyId = $this->currentStudy()->getId();

        $subscription = $this->getSubscriptionModel()->findOne($year, $collegeId, $studyId);

        $reportData = $report->getData($subscription);

        $view = new ViewModel(
            array(
                'year' => $year,
                'heading' => 'National Percentiles',
                'reportData' => $reportData,
                'breakpoints' => $this->getReportService()
                        ->getPercentileBreakPointLabels(),
            )
        );
        $view->setTemplate('mrss/report/max-national.phtml');


        return $view;
    }

    /**
     * If reports are not open for the current study, show an error and redirect
     *
     * @return \Zend\Http\Response
     */
    public function checkReportsAreOpen()
    {
        $open = $this->checkReportAccess();

        if (!$open) {
            $this->flashMessenger()->addErrorMessage(
                'Reports are not currently open. Check back later.'
            );

            return $this->redirect()->toUrl('/members');
        }

        return null;
    }

    /**
     * Return true if they can access reports for the current year
     *
     * @return boolean
     */
    protected function checkReportAccess()
    {
        // Temporarily open them up no matter what.
        return true;

        // Reports are always open for JCCC
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        $impersonationService = $this->getServiceLocator()
            ->get('zfcuserimpersonate_user_service');

        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();

            if ($user->getCollege()->getId() == 101) {
                return true;
            }

            // If an admin is impersonating another user, let them through
            if ($impersonationService->isImpersonated()) {
                return true;
            }
        }

        // Check the current study's report setting
        if (!$this->currentStudy()->getReportsOpen()) {
            return false;
        }
    }

    protected function getErrorLog($shortFormat = false)
    {
        $formatter = new Simple('%message%' . PHP_EOL);

        $writer = new Stream('error.log');

        if ($shortFormat) {
            $writer->setFormatter($formatter);
        }

        $logger = new Logger;
        $logger->addWriter($writer);

        return $logger;
    }

    /**
     * AJAX action for returning a list of possible peer colleges based on the
     * peerGroup stored in the session and the year in the url.
     *
     * @return JsonModel
     */
    public function peerCollegesAction()
    {
        $year = $this->params()->fromRoute('year');
        $benchmarks = $this->params()->fromQuery('benchmarks');
        /** @var \Mrss\Service\Report\Peer $peerService */
        $peerService = $this->getServiceLocator()->get('service.report.peer');

        if (!empty($year)) {
            //$peerGroup = $this->getPeerGroupFromSession();
            //$peerGroup->setYear($year);

            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');

            /*$colleges = $collegeModel->findByPeerGroup(
                $peerGroup,
                $this->currentStudy()
            );*/

            $criteria = $this->getCriteriaFromSession();
            $colleges = $collegeModel->findByCriteria($criteria, $this->currentStudy(), $this->currentCollege(), $year);


            // Lou's issue
            /*$currentUser = $this->zfcUserAuthentication()->getIdentity();
            $this->getErrorLog()->info('Accessing peerCollegesAction as ' . $currentUser->getFullName());
            if (true || $currentUser->getId() == 93) {
                $states = print_r($criteria['states'], true);
                //$nameG = print_r($->getName(), true);
                $collegeNames = '';
                foreach ($colleges as $c) {
                    $collegeNames .= "{$c->getName()} ({$c->getState()})\n";
                }

                $message = "======= Peer demo filter not working ======\n";
                $message .= "States: $states\n";
                //$message .= "Name: $nameG\n";
                $message .= "Colleges:\n$collegeNames\n";

                $this->getErrorLog()->info($message);
            }*/


            if (!empty($benchmarks)) {
                $benchmarkIds = explode(',', $benchmarks);
                $colleges = $peerService->filterCollegesByBenchmarks(
                    $colleges,
                    $benchmarkIds,
                    $year
                );
            }

            $collegeData = array();
            foreach ($colleges as $college) {
                $collegeData[] = array(
                    'name' => $college->getName() . " (" . $college->getState() . ")",
                    'id' => $college->getId()
                );
            }

            return new JsonModel(
                array(
                    'colleges' => $collegeData
                )
            );

        } else {
            $this->flashMessenger()->addErrorMessage('Missing year.');
            return $this->redirect()->toUrl('/');
        }
    }

    public function getPeerBenchmarks($year, $collapse = false)
    {
        $this->longRunningScript();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $benchmarkGroupData = array();
        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            $group = $benchmarkGroup->getName();
            $benchmarkData = array();

            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
            foreach ($benchmarks as $benchmark) {
                // Skip benchmarks that are not on the report
                if (!$benchmark->getIncludeInNationalReport()) {
                    continue;
                }

                // Skip NCCBP form one, with these exceptions:
                $nccbpFormOneInclude = array(
                    'ft_cr_head',
                    'pt_cr_head',
                    'fem_cred_stud',
                    'first_gen_students',
                    'trans_cred',
                    't_c_crh',
                    'dev_crh',
                    'crd_stud_minc',
                    'loc_sour',
                    'state_sour',
                    'tuition_fees_sour'
                );

                if ($benchmarkGroup->getId() == 1 &&
                    !in_array($benchmark->getDbColumn(), $nccbpFormOneInclude)) {
                    continue;
                }


                // Only include benchmarks with at least 5 reported values
                /*$count = $this->getCountOfReportedData(
                    $benchmark->getDbColumn(),
                    $year
                );*/

                $count = 10;

                if ($count >= 5) {
                    $benchmarkData[] = array(
                        'name' => $benchmark->getPeerReportLabel(),
                        'id' => $benchmark->getId()
                    );
                }
            }

            if (count($benchmarkData)) {
                $benchmarkGroupData[$group] = $benchmarkData;
            }
        }

        if ($collapse && !empty($benchmarkGroupData)) {
            $collapsed = array();
            foreach ($benchmarkGroupData as $groupName => $benchmarks) {
                $collapsedBenchmarks = array();
                foreach ($benchmarks as $benchmark) {
                    $collapsedBenchmarks[$benchmark['id']] = $benchmark['name'];
                }

                $collapsed[] = array(
                    'label' => $groupName,
                    'options' => $collapsedBenchmarks
                );
            }

            $benchmarkGroupData = $collapsed;
        }

        return $benchmarkGroupData;
    }

    public function peerBenchmarksAction()
    {
        $year = $this->params()->fromRoute('year');

        if (!empty($year)) {
            $benchmarkGroupData = $this->getPeerBenchmarks($year);

            return new JsonModel(
                array(
                    'benchmarkGroups' => $benchmarkGroupData
                )
            );
        }
    }

    public function trendAction()
    {
        $colleges = array(101,/* 225, 150,*/ 211, 84, 231, 170, 59, 313, 172);
        $dbColumn = 'fall_fall_pers';
        $dbColumn = 'ft_perc_comp';
        $years = range(2007, 2014);

        $report = $this->getReportService()->getTrends($dbColumn, $colleges);

        return array(
            'years' => $years,
            'report' => $report
        );
    }

    public function getCountOfReportedData($dbColumn, $year)
    {
        $observations = $this->getObservations($year);

        /** @var \Mrss\Entity\Study $study */
        $count = 0;
        foreach ($observations as $observation) {
            if (!is_null($observation->get($dbColumn))) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return \Mrss\Model\Observation
     */
    public function getObservationModel()
    {
        return $this->getServiceLocator()->get('model.observation');
    }

    public function getObservations($year)
    {
        if (empty($this->observations[$year])) {
            $observationModel = $this->getObservationModel();

            $this->observations[$year] = $observationModel
                ->findByYearAndStudy($year, $this->currentStudy());
        }

        return $this->observations[$year];
    }

    public function getBenchmarksToExclude()
    {
        return array(
            'institutional_demographics_campus_environment',
            'institutional_demographics_faculty_unionized',
            'institutional_demographics_staff_unionized'
        );
    }

    protected function saveCriteriaToSession($criteria)
    {
        $this->getSessionContainer()->criteria = $criteria;
    }

    protected function getCriteriaFromSession()
    {
        $criteria = $this->getSessionContainer()->criteria;
        if (!$criteria) {
            $criteria = array();
        }

        return $criteria;
    }

    public function savePeerGroupToSession(PeerGroup $peerGroup)
    {
        if ($id = $peerGroup->getId()) {
            $peerGroup = $id;
        }

        $this->getSessionContainer()->peerGroup = $peerGroup;
    }

    public function getPeerGroupFromSession()
    {
        $peerGroup = $this->getSessionContainer()->peerGroup;

        if (!is_object($peerGroup) && intval($peerGroup) > 0) {
            $peerGroup = $this->getPeerGroupModel()->find($peerGroup);
        }

        if (empty($peerGroup)) {
            $peerGroup = new PeerGroup();
        }

        // Always set the college to the current college
        $peerGroup->setCollege($this->currentCollege());
        $peerGroup->setStudy($this->currentStudy());

        return $peerGroup;
    }

    public function getSessionContainer()
    {
        if (empty($this->sessionContainer)) {
            $this->sessionContainer = new Container('report');
        }

        return $this->sessionContainer;
    }

    public function setReportService(Report $service)
    {
        $this->reportService = $service;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReportService()
    {
        if (empty($this->reportService)) {
            $this->reportService = $this->getServiceLocator()
                ->get('service.report');
        }

        return $this->reportService;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->getServiceLocator()->get('model.subscription');
    }

    /**
     * @return \Mrss\Model\PeerGroup
     */
    public function getPeerGroupModel()
    {
        return $this->getServiceLocator()->get('model.peer.group');
    }

    public function observationNotFound()
    {
        $this->flashMessenger()->addErrorMessage(
            'Unable to find membership.'
        );
        return $this->redirect()->toUrl('/members');
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

    protected function getStudyConfig()
    {
        $studyConfig = $this->getServiceLocator()->get('study');

        return $studyConfig;
    }
}
