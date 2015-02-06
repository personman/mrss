<?php

namespace Mrss\Controller;

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

class ReportController extends AbstractActionController
{
    /**
     * @var Report
     */
    protected $reportService;

    protected $sessionContainer;

    protected $observations;

    public function calculateAction()
    {
        $percentileService = $this->getServiceLocator()->get('service.report.percentile');
        $this->longRunningScript();
        $start = microtime(true);

        $years = $percentileService->getCalculationInfo();
        $yearToPrepare = $this->params()->fromRoute('year');

        if (!empty($yearToPrepare)) {
            // Now calculate percentiles
            //$stats = $this->getReportService()->calculateForYear($yearToPrepare);
            $stats = $percentileService->calculateForYear($yearToPrepare);

            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];
            $noData = $stats['noData'];

            $elapsed = round(microtime(true) - $start, 1);

            $this->flashMessenger()->addSuccessMessage(
                "Report prepared for $yearToPrepare. Benchmarks: $benchmarks. Percentiles: $percentiles.
                Percentile ranks: $percentileRanks. Benchmarks without data: $noData.
                Elapsed time: $elapsed seconds."
            );

            return $this->redirect()->toRoute('reports/calculate');
        }

        return array(
            'years' => $years
        );
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
            $stats = $this->getServiceLocator()->get('service.report.percentile')
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

    public function calculateOutliersAction()
    {
        $this->longRunningScript();

        $yearToPrepare = $this->params()->fromRoute('year');

        $stats = $this->getServiceLocator()->get('service.report.outliers')
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

    public function adminOutliersAction()
    {
        $outlierReport = $this->getServiceLocator()->get('service.report.outliers')
            ->getAdminOutlierReport();

        return array(
            'report' => $outlierReport
        );
    }

    public function outlierAction()
    {
        $college = $this->currentCollege();
        $outlierReport = $this->getServiceLocator()->get('service.report.outliers')
            ->getOutlierReport($college);

        return array(
            'report' => $outlierReport
        );
    }

    public function emailOutliersAction()
    {
        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');
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
        ini_set('memory_limit', '512M');
        set_time_limit(3600);
    }

    public function nationalAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
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

        $observation = $this->currentObservation($year);
        $reportData = $this->getServiceLocator()->get('service.report.national')
            ->getData($observation, $system);

        // HTML or Excel?
        $format = $this->params()->fromRoute('format');

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
            'college' => $observation->getCollege(),
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

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()->getSummaryReportData($observation);

        return array(
            'reportData' => $reportData,
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

        $year = $this->getYearFromRouteOrStudy();

        $ipeds = $this->params()->fromRoute('ipeds');
        if ($ipeds) {
            /** @var \Mrss\Model\college $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $college = $collegeModel->findOneByIpeds($ipeds);
        }

        if (empty($college)) {
            $college = $this->currentCollege();
        }

        //$this->view->headTitle('test');

        // Nccbp migration: temporary
        /*if ($year < 2014 && !$this->isAllowed('adminMenu', 'view')) {
            $this->flashMessenger()->addErrorMessage(
                "Reports prior to 2014 are under review and will be available soon."
            );
            return $this->redirect()->toUrl('/reports/executive/2014');
        }*/


        $subscriptions = $college->getSubscriptionsForStudy($this->currentStudy());

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

    public function getYearFromRouteOrStudy()
    {
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            /*$year = $this->currentStudy()->getCurrentYear();

            // But if reports aren't open yet, show them last year's by default

            $isJCCC = (!empty($college) && $college->getId() == 101);
            if (/*!$isJCCC && *//*!$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }*/


            // New
            /** @var \Mrss\Model\Subscription $subModel */
            $subModel = $this->getServiceLocator()->get('model.subscription');
            $college = $this->currentCollege();
            $before = null;
            if (!$this->currentStudy()->getReportsOpen()) {
                $before = $this->currentStudy()->getCurrentYear();
            }

            $latestSubscription = $subModel->getLatestSubscription($this->currentStudy(), $college->getId(), $before);

            $year = $latestSubscription->getYear();
        }

        return $year;
    }

    public function peerAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $peerService = $this->getServiceLocator()->get('service.report.peer');
        $years = $peerService->getYearsWithSubscriptions();

        // If reports are closed, remove the last year
        if (!$this->currentStudy()->getReportsOpen()) {
            array_shift($years);
        }

        $s = microtime(1);
        $defaultBenchmarks = $this->getPeerBenchmarks($years[0], true);
        $e = microtime(1) - $s;
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
                $peerGroup->setBenchmarks($data['benchmarks']);
                $peerGroup->setPeers($data['peers']);

                // Save to db?
                if ($name = $data['name']) {
                    $college = $this->currentCollege();
                    $peerGroup->setName($name);
                    $peerGroup->setCollege($college);

                    // See if it exists
                    $existingGroup = $this->getPeerGroupModel()
                        ->findOneByCollegeAndName($college, $name);

                    if ($existingGroup) {
                        $peerGroup->setId($existingGroup->getId());
                        $this->getPeerGroupModel()->getEntityManager()
                            ->merge($peerGroup);
                    } else {
                        $this->getPeerGroupModel()->save($peerGroup);
                    }

                    $this->getPeerGroupModel()->getEntityManager()->flush();

                    $this->flashMessenger()->addSuccessMessage(
                        "The peer group $name has been saved."
                    );
                }

                $this->savePeerGroupToSession($peerGroup);

                return $this->redirect()->toRoute('reports/peer-results');
            }
        }

        // Prepare saved peer groups for javascript
        $peerGroups = array();
        foreach ($this->currentCollege()->getPeerGroups() as $group) {
            $peerGroups[] = array(
                'name' => $group->getName(),
                'id' => $group->getId(),
                'peers' => $group->getPeers()
            );
        }
        $peerGroups = json_encode($peerGroups);

        return array(
            'form' => $form,
            'peerGroup' => $peerGroup,
            'peerGroups' => $peerGroups
        );
    }

    public function peerResultsAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $peerService = $this->getServiceLocator()->get('service.report.peer');

        $format = $this->params()->fromRoute('format');

        ini_set('memory_limit', '512M');
        $peerGroup = $this->getPeerGroupFromSession();

        $report = $peerService->getPeerReport($peerGroup);

        if ($format == 'excel') {
            $peerService->downloadPeerReport($report, $peerGroup);
        }

        return array(
            'peerGroup' => $peerGroup,
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

        $form = new PeerComparisonDemographics($this->currentStudy()->getId());
        $peerGroup = $this->getPeerGroupFromSession();

        $em = $this->getServiceLocator()->get('em');

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\PeerGroup'));
        $form->bind($peerGroup);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();

            // Handle empty multiselects
            $multiselects = array(
                'states',
                'environments',
                'facultyUnionized',
                'staffUnionized',
                'institutionalType',
                'institutionalControl'
            );

            foreach ($multiselects as $multiselect) {
                if (empty($postData[$multiselect])) {
                    $postData[$multiselect] = array();
                }
            }

            $form->setData($postData);

            if ($form->isValid()) {
                $this->savePeerGroupToSession($peerGroup);

                return $this->redirect()->toRoute('reports/peer');
            }
        }

        return array(
            'form' => $form
        );
    }

    public function exploreAction()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $benchmarks = array();
        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            $group = array(
                'label' => $benchmarkGroup->getName(),
                'options' => array()
            );

            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                $group['options'][$benchmark->getDbColumn()] = $benchmark->getName();
            }

            $benchmarks[$benchmarkGroup->getId()] = $group;
        }


        $colleges = array();

        $form = new Explore($benchmarks, $colleges);

        $chart = null;
        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $benchmark1 = $data['benchmark1'];
                $benchmark2 = $data['benchmark2'];
                $size = $data['benchmark3'];
                $title = $data['title'];

                $chart = $this->getReportService()
                    ->getBubbleChart($benchmark1, $benchmark2, $size, $title);
            }
        }



        return array(
            'form' => $form,
            'chart' => $chart
        );
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

    /**
     * If reports are not open for the current study, show an error and redirect
     *
     * @return \Zend\Http\Response
     */
    public function checkReportsAreOpen()
    {
        return null;
        // Reports are always open for JCCC
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        $impersonationService = $this->getServiceLocator()
            ->get('zfcuserimpersonate_user_service');

        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();

            if ($user->getCollege()->getId() == 101) {
                return null;
            }

            // If an admin is impersonating another user, let them through
            if ($impersonationService->isImpersonated()) {
                return null;
            }
        }

        // Check the current study's report setting
        if (!$this->currentStudy()->getReportsOpen()) {
            $this->flashMessenger()->addErrorMessage(
                'Reports are not currently open. Check back later.'
            );

            return $this->redirect()->toUrl('/members');
        }
    }

    protected function checkReportAccess()
    {

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
        $peerService = $this->getServiceLocator()->get('service.report.peer');

        if (!empty($year)) {
            $peerGroup = $this->getPeerGroupFromSession();
            $peerGroup->setYear($year);

            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');

            $colleges = $collegeModel->findByPeerGroup(
                $peerGroup,
                $this->currentStudy()
            );

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
            die('Missing year.');
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

    public function getObservations($year)
    {
        if (empty($this->observations[$year])) {
            /** @var \Mrss\Model\Observation $observationModel */
            $observationModel = $this->getServiceLocator()->get('model.observation');

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

    public function savePeerGroupToSession(PeerGroup $peerGroup)
    {
        $this->getSessionContainer()->peerGroup = $peerGroup;
    }

    public function getPeerGroupFromSession()
    {
        $peerGroup = $this->getSessionContainer()->peerGroup;

        if (empty($peerGroup)) {
            $peerGroup = new PeerGroup();
        }

        // Always set the college to the current college
        $peerGroup->setCollege($this->currentCollege());

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
        return $this->getServiceLocator()->get('model.peerGroup');
    }

    public function observationNotFound()
    {
        $this->flashMessenger()->addErrorMessage(
            'Unable to find membership.'
        );
        return $this->redirect()->toUrl('/members');
    }
}
