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

class ReportController extends AbstractActionController
{
    /**
     * @var Report
     */
    protected $reportService;

    protected $sessionContainer;

    public function calculateAction()
    {
        $this->longRunningScript();
        $start = microtime(true);

        $years = $this->getReportService()->getCalculationInfo();
        $yearToPrepare = $this->params()->fromRoute('year');

        if (!empty($yearToPrepare)) {
            // Now calculate percentiles
            $stats = $this->getReportService()->calculateForYear($yearToPrepare);
            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];
            $noData = $stats['noData'];

            $elapsed = round(microtime(true) - $start, 1);

            $this->flashMessenger()->addSuccessMessage(
                "Report prepared. Benchmarks: $benchmarks. Percentiles: $percentiles.
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
            $stats = $this->getReportService()->calculateSystems($yearToPrepare);
            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];
            $noData = $stats['noData'];
            $systems = $stats['systems'];

            $elapsed = round(microtime(true) - $start, 1);

            $this->flashMessenger()->addSuccessMessage(
                "Reports prepared for $systems systems. Benchmarks: $benchmarks.
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

        $stats = $this->getReportService()->calculateOutliersForYear($yearToPrepare);
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
        $outlierReport = $this->getReportService()->getAdminOutlierReport();

        return array(
            'report' => $outlierReport
        );
    }

    public function outlierAction()
    {
        $college = $this->currentCollege();
        $outlierReport = $this->getReportService()->getOutlierReport($college);

        return array(
            'report' => $outlierReport
        );
    }

    public function emailOutliersAction()
    {
        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');

        $task = $this->params()->fromRoute('task');
        if ($task == 'preview') {
            $stats = $this->getReportService()->emailOutliers($renderer, false);
        } elseif ($task == 'send') {
            $stats = $this->getReportService()->emailOutliers($renderer);

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
            throw new \Exception('Subscription not found for year ' . $year);
        }

        // Nccbp migration: temporary
        if ($year < 2014 && !$this->isAllowed('adminMenu', 'view')) {
            $this->flashMessenger()->addErrorMessage(
                "Reports prior to 2014 are under review and will be available soon."
            );
            return $this->redirect()->toUrl('/reports/national/2014');
        }

        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()
            ->getNationalReportData($observation, $system);

        // HTML or Excel?
        $format = $this->params()->fromRoute('format');

        if ($format == 'excel') {
            $this->getReportService()->downloadNationalReport($reportData, $system);
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

    public function executiveAction()
    {
        $open = (!empty($_GET['open']));
        $year = $this->getYearFromRouteOrStudy();
        $college = $this->currentCollege();

        // Nccbp migration: temporary
        if ($year < 2014 && !$this->isAllowed('adminMenu', 'view')) {
            $this->flashMessenger()->addErrorMessage(
                "Reports prior to 2014 are under review and will be available soon."
            );
            return $this->redirect()->toUrl('/reports/executive/2014');
        }


        $subscriptions = $college->getSubscriptionsForStudy($this->currentStudy());

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation($year);
        $this->getReportService()->setObservation($observation);
        $reportData = $this->getReportService()->getExecutiveReportData();

        return array(
            'reportData' => $reportData,
            'year' => $year,
            'subscriptions' => $subscriptions,
            'college' => $college,
            'open' => $open,
        );
    }

    public function getYearFromRouteOrStudy()
    {
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // But if reports aren't open yet, show them last year's by default
            $college = $this->currentCollege();
            $isJCCC = ($college->getId() == 101);
            if (/*!$isJCCC && */!$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }
        }

        return $year;
    }

    public function peerAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $years = $this->getReportService()->getYearsWithSubscriptions();

        // If reports are closed, remove the last year
        if (!$this->currentStudy()->getReportsOpen()) {
            array_shift($years);
        }

        $form = new PeerComparison(
            $years
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

        $format = $this->params()->fromRoute('format');

        ini_set('memory_limit', '512M');
        $peerGroup = $this->getPeerGroupFromSession();

        $report = $this->getReportService()->getPeerReport($peerGroup);

        if ($format == 'excel') {
            $this->getReportService()->downloadPeerReport($report, $peerGroup);
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

        $form = new PeerComparisonDemographics;
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
                $colleges = $this->getReportService()->filterCollegesByBenchmarks(
                    $colleges,
                    $benchmarkIds,
                    $year
                );
            }

            $collegeData = array();
            foreach ($colleges as $college) {
                $collegeData[] = array(
                    'name' => $college->getName(),
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

    public function peerBenchmarksAction()
    {
        $year = $this->params()->fromRoute('year');

        if (!empty($year)) {
            /** @var \Mrss\Entity\Study $study */
            $study = $this->currentStudy();

            $benchmarkGroupData = array();
            foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
                $group = $benchmarkGroup->getName();
                $benchmarkData = array();

                $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
                foreach ($benchmarks as $benchmark) {
                    // Only include benchmarks with at least 5 reported values
                    $count = $this->getCountOfReportedData(
                        $benchmark->getDbColumn(),
                        $year
                    );

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

            return new JsonModel(
                array(
                    'benchmarkGroups' => $benchmarkGroupData
                )
            );
        }
    }

    public function getCountOfReportedData($dbColumn, $year)
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $count = 0;
        foreach ($study->getSubscriptionsForYear($year) as $subscription) {
            $observation = $subscription->getObservation();
            if (!is_null($observation->get($dbColumn))) {
                $count++;
            }
        }

        return $count;
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
}
