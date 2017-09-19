<?php

namespace Mrss\Controller;

use Mrss\Form\PeerComparisonDemographics;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Service\Report;
use Mrss\Form\PeerComparison;
use Mrss\Entity\PeerGroup;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ReportController extends ReportAdminController
{
    protected $sessionContainer;

    protected $observations;

    /**
     * For an individual institution
     *
     * Replaced by nationalAction below
     *
     * @return array
     */
    public function percentChangeAction()
    {
        takeYourTime();
        $format = $this->params()->fromRoute('format');

        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        $college = $this->currentCollege();

        /** @var \Mrss\Model\PercentChange $percentChangeModel */
        $percentChangeModel = $this->getServiceLocator()->get('model.percentchange');
        $changes = $percentChangeModel->findByCollegeAndYear($college, $year);

        $changes = $this->getPercentChangeService()->getReport($changes, $year);


        if ($format == 'excel') {
            $this->getPercentChangeService()->download($changes);
            die;
        }

        return array(
            'reportData' => $changes,
            'changes' => $changes,
            'year' => $year
        );
    }

    public function outlierAction()
    {
        $system = null;
        if ($this->getStudyConfig()->use_structures) {
            $system = $this->getActiveSystem();
        }

        $college = $this->currentCollege();
        $year = $this->currentStudy()->getCurrentYear();
        $outlierReport = $this->getServiceLocator()->get('service.report.outliers')
            ->getOutlierReport($college, $system);

        $studyName = $this->currentStudy()->getName();
        if ($system) {
            $studyName = $system->getName();
        }

        $issues = $this->getIssueModel()->findByCollege($college, $year);

        return array(
            'report' => $outlierReport,
            'studyName' => $studyName,
            'year' => $year,
            'showDetails' => true,
            'system' => $system,
            'issues' => $issues
        );
    }

    public function nationalAction()
    {
        // HTML or Excel?
        $format = $this->params()->fromRoute('format');
        $year = $this->getYearFromRouteOrStudy();
        $benchmarkGroupId = $this->params()->fromRoute('benchmarkGroupId', null);

        $forPercentChange = $this->params()->fromRoute('forPercentChange');

        if ($redirect = $this->checkReportsAreOpen(true, $year)) {
            return $redirect;
        }

        if ($this->currentStudy()->getId() == 2 && $format != 'excel') {
            return $this->maxNationalAction();
        }

        // Is this a system report?
        $systemVersion = $this->params()->fromRoute('system');
        $system = null;
        $otherSystems = array();
        if ($systemVersion) {
            // Confirm they're actually part of a system
            $systemId = $this->getActiveSystemId();

            if (empty($systemId)) {
                $this->flashMessenger()->addErrorMessage(
                    'Your institution is not part of a system.'
                );

                return $this->redirect()->toUrl('/members');
            } else {
                $system = $this->getSystemModel()->find($systemId);

                $systems = $this->currentCollege()->getSystems();
                foreach ($systems as $otherSystem) {
                    //if ($systemId != $otherSystem->getId()) {
                        $otherSystems[] = $otherSystem;
                    //}
                }
            }
        }

        $subscriptions = $this->getSubscriptions();

        $subscription = $this->getSubscriptionByYear($year);

        if (empty($subscription)) {
            //throw new \Exception('Subscription not found for year ' . $year);

            return $this->observationNotFound();
        }

        $reportPath = 'national';
        if ($system) {
            $reportPath = 'system';
        }

        if ($forPercentChange) {
            $reportService = $this->getPercentChangeService();
            $reportPath = 'percent-change';
        } else {
            $reportService = $this->getNationService($forPercentChange);
        }

        $reportData = $reportService->getData($subscription, $system, $benchmarkGroupId);


        // Download?
        if ($format == 'excel') {
            $reportService->download($reportData, $system);
            die;
        }

        if ($forPercentChange) {
            $subscriptions = $this->getSubscriptionsForPercentChange($subscriptions);
        }

        if ($system) {
            $subscriptions = $this->getSubscriptionsForSystem($subscriptions, $system);
        }

        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'reportData' => $reportData,
            'college' => $subscription->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels(),
            'system' => $system,
            'otherSystems' => $otherSystems,
            'reportPath' => $reportPath,
            'forPercentChange' => $forPercentChange,
            'studyConfig' => $this->getStudyConfig(),
            'benchmarkGroupId' => $benchmarkGroupId
        );
    }

    protected function getSubscriptions()
    {
        $system = null;
        if ($this->getServiceLocator()->get('Study')->use_structures) {
            $system = $this->getActiveSystem();
        }

        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->currentStudy(), true, $system);

        return $subscriptions;
    }

    /**
     * @param \Mrss\Entity\Subscription[] $subscriptions
     * @param $system
     */
    protected function getSubscriptionsForSystem($subscriptions, $system)
    {
        $newSubscriptions = array();
        foreach ($subscriptions as $subscription) {
            $year = $subscription->getYear();
            if ($subscription->getCollege()->hasSystemMembership($system->getId(), $year)) {
                $newSubscriptions[] = $subscription;
                //pr($subscription->getYear());
            }
        }

        return $newSubscriptions;
    }

    /**
     * Remove subscriptions where they didn't have one in the prior year
     *
     * @param $subscriptions
     */
    protected function getSubscriptionsForPercentChange($subscriptions)
    {
        $years = array();
        foreach ($subscriptions as $subscription) {
            $years[] = $subscription->getYear();
        }

        $newSubscriptions = array();
        foreach ($subscriptions as $subscription) {
            $priorYear = $subscription->getYear() - 1;

            if (in_array($priorYear, $years)) {
                $newSubscriptions[] = $subscription;
            }
        }

        return $newSubscriptions;
    }

    /** @return \Mrss\Service\Report\National */
    protected function getNationService()
    {
        return $this->getServiceLocator()->get('service.report.national');
    }

    protected function getSubscriptionByYear($year)
    {
        return $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege(), $this->currentStudy());
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
            $open = true;
        }

        if (empty($college)) {
            $college = $this->currentCollege();
        }


        $year = $this->getYearFromRouteOrStudy($college);

        $subscriptions = $college->getSubscriptionsForStudy($this->currentStudy());

        // Don't show current executive report yet @todo: use a study setup checkbox for this
        $yearToSkip = null;//2017;
        if (!$open) {
            $yearToSkip = $this->currentStudy()->getCurrentYear();
        }

        $newSubs = array();
        foreach ($subscriptions as $subscription) {
            if ($subscription->getYear() != $yearToSkip) {
                $newSubs[] = $subscription;
            }
        }
        $subscriptions = $newSubs;

        if ($year == $yearToSkip) {
            $year = $newSubs[0]->getYear();
        }

        if ($year != $yearToSkip) {
            $open = true;
        }

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

        $autoPrint = false;
        if (empty($ipeds) && $forcePrintStyles) {
            $autoPrint = true;
        }

        // Membership count
        $memberCount = $this->getMemberCount($year);

        $view = new ViewModel(
            array(
                'reportData' => $reportData,
                'year' => $year,
                'subscriptions' => $subscriptions,
                'college' => $college,
                'open' => $open,
                'media' => $media,
                //'memberCount' => $memberCount,
                'autoPrint' => $autoPrint
            )
        );
        $view->setTemplate('mrss/report/executive.phtml');

        return $view;
    }

    public function getMemberCount($year)
    {
        $members = $this->currentStudy()->getSubscriptionsForYear($year);

        return count($members);
    }

    public function peerAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $subs = $this->getSubscriptions();
        $years = array();
        foreach ($subs as $sub) {
            if (!$study->getReportsOpen() && $sub->getYear() == $study->getCurrentYear()) {
                continue;
            }
            $years[] = $sub->getYear();
        }
        rsort($years);

        $defaultBenchmarks = $this->getPeerBenchmarks($years[0], true);

        // Is this a system report?
        $systemVersion = $this->getStudyConfig()->use_structures;
        $system = null;
        $otherSystems = array();
        if ($systemVersion) {
            // Confirm they're actually part of a system
            $systemId = $this->getActiveSystemId();

            if (empty($systemId)) {
                $this->flashMessenger()->addErrorMessage(
                    'Your ' . $this->getStudyConfig()->institution_label . ' is not part of a system.'
                );

                return $this->redirect()->toUrl('/members');
            } else {
                $system = $this->getSystemModel()->find($systemId);

                $systemMemberships = $this->currentCollege()->getSystems();
                foreach ($systemMemberships as $systemMembership) {
                    //if ($systemId != $systemMembership->getId()) {
                        $otherSystems[] = $systemMembership;
                    //}
                }
            }
        }


        $form = new PeerComparison(
            $years,
            $defaultBenchmarks,
            $this->getStudyConfig()
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
            'criteria' => $criteria,
            'system' => $system,
            'otherSystems' => $otherSystems
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

        $config = $this->getStudyConfig();
        $includePercentiles = $config->peer_percentiles;


        /** @var \Mrss\Service\Report\Peer $peerService */
        $peerService = $this->getServiceLocator()->get('service.report.peer');
        $peerService->setIncludePercentiles($includePercentiles);

        $format = $this->params()->fromRoute('format');

        $benchmarks = $this->getSessionContainer()->benchmarks;
        $peers = $this->getSessionContainer()->peers;
        $year = $this->getSessionContainer()->year;

        $peerGroupName = $this->getSessionContainer()->peerGroupName;
        $peerService->setShowPeerDataYouDidNotSubmit($this->getStudyConfig()->show_peer_data_you_did_not_submit);

        $report = $peerService->getPeerReport($benchmarks, $peers, $this->currentCollege(), $year, $peerGroupName);

        if ($format == 'excel') {
            $peerService->downloadPeerReport($report, $this->getStudyConfig());
        }

        return array(
            'peerGroupName' => $peerGroupName,
            'report' => $report,
            'studyConfig' => $this->getStudyConfig(),
            'includePercentiles' => $includePercentiles
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

        $form = new PeerComparisonDemographics($this->currentStudy(), $this->getStudyConfig());

        $criteria = $this->getCriteriaFromSession();
        $form->setData($criteria);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            unset($postData['buttons']);

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

    public function bestPerformersAction()
    {
        /** @var \Mrss\Service\Report\BestPerformers $report */
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
            'reportData' => $report->getBenchmarks($subscription)
        );
    }

    public function strengthsAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }


        $threshold = 75;

        /** @var \Mrss\Service\Report\Executive $report */
        $report = $this->getServiceLocator()->get('service.report.executive');
        $year = $this->getYearFromRouteOrStudy();

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentCollege()->getObservationForYear($year);
        $report->setObservation($observation);

        $activeSystem = $this->getActiveSystem();
        $report->setSystem($activeSystem);


        $strengths = $report->getStrengths(false, $threshold);
        $weaknesses = $report->getWeaknesses($threshold);

        $subscriptions = $this->getSubscriptions();

        $subscription = $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege(), $this->currentStudy());

        if (empty($subscription)) {
            return $this->observationNotFound();
        }


        $system = null;
        $otherSystems = array();
        if ($this->getStudyConfig()->use_structures) {
            // Confirm they're actually part of a system
            $systemId = $this->getActiveSystemId();

            if (empty($systemId)) {
                $this->flashMessenger()->addErrorMessage(
                    'Your institution is not part of a system.'
                );

                return $this->redirect()->toUrl('/members');
            } else {
                $system = $this->getSystemModel()->find($systemId);

                $systems = $this->currentCollege()->getSystems();
                foreach ($systems as $otherSystem) {
                    $otherSystems[] = $otherSystem;
                }
            }
        }


        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'system' => $system,
            'otherSystems' => $otherSystems,
            'threshold' => $threshold,
            'lowThreshold' => 100 - $threshold,
            'reportUrl' => ($this->getStudyConfig()->use_structures) ? 'network' : 'national'
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
    public function checkReportsAreOpen($freeReport = false, $year = null)
    {
        $open = $this->checkReportAccess($freeReport, $year);

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
    protected function checkReportAccess($freeReport = false, $year = null)
    {
        if ($this->getStudyConfig()->college_report_access_checkbox && !$freeReport) {
            if ($college = $this->getCollege()) {
                if (!$college->hasReportAccess()) {
                    return false;
                }
            }
        }

        if ($this->getStudyConfig()->use_structures) {
            $system = $this->getActiveSystem();
            if (!$system->getReportsOpen() && $system->getCurrentYear() == $year) {
                return false;
            }
        }

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
        if ($this->getStudyConfig()->use_structures) {

        } elseif (!$this->currentStudy()->getReportsOpen()) {
            return false;
        }
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

            $criteria = $this->getCriteriaFromSession();
            $colleges = $collegeModel->findByCriteria($criteria, $this->currentStudy(), $this->currentCollege(), $year);


            if (!empty($benchmarks)) {
                $benchmarkIds = explode(',', $benchmarks);
                $colleges = $peerService->filterCollegesByBenchmarks(
                    $colleges,
                    $benchmarkIds,
                    $year
                );
            }

            $colleges = $this->filterCollegesBySystem($colleges, $year);

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

    protected function filterCollegesBySystem($colleges, $year)
    {
        if ($this->getStudyConfig()->use_structures) {
            $filteredColleges = array();

            $activeSystem = $this->getActiveSystem();

            $memberColleges = $activeSystem->getMemberColleges();
            foreach ($colleges as $college) {
                if (!empty($memberColleges[$college->getId()])) {
                    $memberCollegeInfo = $memberColleges[$college->getId()];
                    if (in_array($year, $memberCollegeInfo['years'])) {
                        $filteredColleges[] = $college;
                    }
                }
            }

            $colleges = $filteredColleges;
        }

        return $colleges;
    }

    public function getPeerBenchmarks($year, $collapse = false)
    {
        $this->longRunningScript();

        $subscription = $this->getSubscriptionByYear($year);

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $benchmarkGroupData = array();
        foreach ($this->getBenchmarkGroups($subscription) as $benchmarkGroup) {
            $group = $benchmarkGroup->getName();
            $benchmarkData = array();

            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
            foreach ($benchmarks as $benchmark) {
                // Skip benchmarks that are not on the report
                if (!$benchmark->getIncludeInNationalReport()) {
                    continue;
                }

                // @todo: generalize
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
                    'tuition_fees_sour',
                    'hs_stud_hdct'
                );

                if (($this->currentStudy()->getId() == 1 &&$benchmarkGroup->getId() == 1) &&
                    !in_array($benchmark->getDbColumn(), $nccbpFormOneInclude)) {
                    continue;
                }


                // Only include benchmarks with at least 5 reported values
                /*$count = $this->getCountOfReportedData(
                    $benchmark->getDbColumn(),
                    $year
                );*/

                $count = 10;

                if ($count >= $this->getStudyConfig()->min_peers) {
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
