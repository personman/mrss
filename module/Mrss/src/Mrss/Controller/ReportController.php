<?php

namespace Mrss\Controller;

use Mrss\Form\PeerComparisonDemographics;
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
        $years = $this->getReportService()->getYearsWithSubscriptions();
        $yearToPrepare = $this->params()->fromRoute('year');

        if (!empty($yearToPrepare)) {
            // First recalc all computed fields
            $computedFieldsService = $this->getServiceLocator()->get('computedFields');
            $subs = $this->getServiceLocator()->get('model.subscription')
                ->findByStudyAndYear($this->currentStudy(), $yearToPrepare);
            foreach ($subs as $sub) {
                $observation = $sub->getObservation();
                $computedFieldsService->calculateAllForObservation($observation);
            }

            // Now calculate percentiles
            $stats = $this->getReportService()->calculateForYear($yearToPrepare);
            $benchmarks = $stats['benchmarks'];
            $percentiles = $stats['percentiles'];
            $percentileRanks = $stats['percentileRanks'];

            $this->flashMessenger()->addSuccessMessage(
                "Report prepared. Benchmarks: $benchmarks. Percentiles: $percentiles.
                Percentile ranks: $percentileRanks."
            );

            return $this->redirect()->toRoute('reports/calculate');
        }

        return array(
            'years' => $years
        );
    }

    public function nationalAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $year = $this->getYearFromRouteOrStudy();

        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()->getNationalReportData($observation);

        return array(
            'reportData' => $reportData,
            'college' => $observation->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels()
        );
    }

    public function summaryAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $year = $this->getYearFromRouteOrStudy();

        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()->getSummaryReportData($observation);

        return array(
            'reportData' => $reportData,
            'college' => $observation->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels()
        );
    }

    public function getYearFromRouteOrStudy()
    {
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        return $year;
    }

    public function peerAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        $form = new PeerComparison;

        $peerGroup = $this->getPeerGroupFromSession();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $peerGroup->setYear($data['reportingPeriod']);
                $peerGroup->setBenchmarks($data['benchmarks']);
                $peerGroup->setPeers($data['peers']);

                $this->savePeerGroupToSession($peerGroup);

                return $this->redirect()->toRoute('reports/peer-results');

                //var_dump($data); die;
            }
        }

        return array(
            'form' => $form,
            'peerGroup' => $peerGroup
        );
    }

    public function peerResultsAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }

        ini_set('memory_limit', '512M');
        $peerGroup = $this->getPeerGroupFromSession();

        $report = $this->getReportService()->getPeerReport($peerGroup);

        return array(
            'peerGroup' => $peerGroup,
            'report' => $report
        );
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
            if (empty($postData['states'])) {
                $postData['states'] = array();
            }
            if (empty($postData['environments'])) {
                $postData['environments'] = array();
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

    /**
     * If reports are not open for the current study, show an error and redirect
     *
     * @return \Zend\Http\Response
     */
    public function checkReportsAreOpen()
    {
        if (!$this->currentStudy()->getReportsOpen()) {
            $this->flashMessenger()->addErrorMessage(
                'Reports are not currently open. Check back later.'
            );

            return $this->redirect()->toUrl('/members');
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

            $benchmarks = $study->getBenchmarksForYear($year);

            $benchmarkData = array();
            foreach ($benchmarks as $benchmark) {
                // Skip some demographic data
                if (in_array($benchmark->getDbColumn(), $this->getBenchmarksToExclude())) {
                    continue;
                }

                $benchmarkData[] = array(
                    'name' => $benchmark->getName(),
                    'id' => $benchmark->getId()
                );
            }

            return new JsonModel(
                array(
                    'benchmarks' => $benchmarkData
                )
            );
        }
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

    public function getReportService()
    {
        if (empty($this->reportService)) {
            $this->reportService = $this->getServiceLocator()
                ->get('service.report');
        }

        return $this->reportService;
    }
}
