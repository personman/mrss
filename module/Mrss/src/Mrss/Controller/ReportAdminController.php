<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Service\Report;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Formatter\Simple;

class ReportAdminController extends AbstractActionController
{
    /**
     * @var Report
     */
    protected $reportService;

    protected $debug = false;

    /**
     * @return Report\Percentile
     */
    protected function getPercentileService()
    {
        $percentileService = $this->getServiceLocator()->get('service.report.percentile');

        return $percentileService;
    }

    /**
     * @return \Mrss\Service\ComputedFields
     */
    protected function getComputedFieldsService()
    {
        $service = $this->getServiceLocator()->get('computedFields');

        return $service;
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
        $collegeIds = array();
        $benchmarkIds = array();
        $systemIds = array();
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


                $break = "Mom";
                $name = $sub->getCollege()->getName();
                $result = strcasecmp($name, $break);

                //pr($break); pr($name); pr(

                if ($result > 0) {
                    $collegeIds[$year][] = $sub->getCollege()->getId();
                }
            }

            $observationIds[$year] = $yearIds;


            // Get the Ids of benchmarks on the report
            foreach ($this->getBenchmarkModel()->findOnReport() as $benchmark) {
                $benchmarkIds[$year][] = $benchmark->getId();
            }

            $systems = $this->getSystemModel()->findWithSubscription($year, $this->currentStudy()->getId());
            $systemIds[$year] = array();
            foreach ($systems as $system) {
                $systemIds[$year][] = $system->getId();
            }


        }

        // Get System ids
        $currentYear = $this->currentStudy()->getCurrentYear();


        // Limit college ids for AAUP (one time thing, then remove)
        /*$newColleges = array();
        foreach ($collegeIds["2017"] as $collegeId) {
            if ($collegeId > 2379) {
                $newColleges[] = $collegeId;
            }
        }
        $collegeIds["2017"] = $newColleges;*/

        return array(
            'years' => $years,
            'study' => $this->currentStudy(),
            'observationIds' => $observationIds,
            'systemIds' => $systemIds,
            'collegeIds' => $collegeIds,
            'benchmarkIds' => $benchmarkIds
        );
    }

    public function debug($message, $var)
    {
        if ($this->debug) {
            $seconds = microtime(true) - REQUEST_MICROTIME;

            echo "$seconds since request started.<h3>$message</h3>";

            if ($var) {
                pr($var);
            }
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
        $forPercentChange = $this->params()->fromRoute('forPercentChange', false);

        $percentileService = $this->getPercentileService();
        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

        // If this is the first benchmark, clear existing percentiles
        if ($position == 'first') {
            $percentileService->clearPercentiles($year, null, $forPercentChange);
        }

        // Last?
        if ($position == 'last') {
            $settingKey = $this->getReportService()->getReportCalculatedSettingKey($year, null, $forPercentChange);
            $this->getReportService()->getSettingModel()->setValueForIdentifier($settingKey, date('c'));
        }


        $this->debug("About to calculate", $benchmarkId);
        $this->debug("For percent change: ", $forPercentChange);

        // Now actually calculate and save percentiles
        $percentileService->calculateForBenchmark($benchmark, $year, null, $forPercentChange);

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
        $debug = $this->params()->fromRoute('debug', false);

        takeYourTime();


        if (!$debug) {
            $this->disableQueryLogging();
        }

        $observationId = $this->params()->fromRoute('observation');

        $debugColumn = $this->params()->fromRoute('benchmark');

        $status = 'ok';

        $observation = $this->getObservationModel()->find($observationId);


        if ($observation) {
            $service = $this->getComputedFieldsService();
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


        $viewParams = array(
            'status' => $status,
            'observation' => $observationId
        );

        if ($debug) {
            $logger = $this->getObservationModel()->getEntityManager()->getConfiguration()->getSQLLogger();

            $this->queryLogger($logger);
            $viewParams['logger'] = $logger;

            $view = new ViewModel($viewParams);
            $view->setTerminal(true);
        } else {
            $view = new JsonModel($viewParams);
        }

        return $view;
    }

    public function disableQueryLogging()
    {
        // Turn off query logging
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);
    }


    protected function queryLogger($logger)
    {
        $tables = array();
        $params = array();

        foreach ($logger->queries as $query) {
            $sql = $query['sql'];
            //pr($sql);

            $table = $this->getTableFromSql($sql);

            if (empty($tables[$table])) {
                $tables[$table] = 1;
            } else {
                $tables[$table]++;
            }

            $qParams = $query['params'];
            if ($table && isset($qParams[0])) {
                $param = $qParams[0];

                if ($param == 'ft_male_no_rank_number_12_month') {
                    //pr($sql);
                }

                if (!empty($paaram)) {
                    if (empty($params[$param])) {
                        $params[$param] = 1;
                    } else {
                        $params[$param]++;
                    }
                }

            }
        }

        pr($tables);

        asort($params);
        //pr($params);

        //die('tewt');
    }

    protected function getTableFromSql($sql)
    {
        preg_match('/(FROM|UPDATE) (.*?) /', $sql, $matches);

        $table = null;
        if (!empty($matches[2])) {
            $table = $matches[2];
        }

        return $table;
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
     * For admins. Shows all institutions.
     *
     * @return array
     */
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
     * @return \Mrss\Service\Report\Changes
     */
    protected function getPercentChangeService()
    {
        return $this->getServiceLocator()->get('service.report.changes');
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

    protected function sendOutlierAction()
    {
        $collegeId = $this->params()->fromRoute('college');
        $year = $this->params()->fromRoute('year');

        $renderer = $this->getRenderer();
        $stats = $this->getOutlierService()->emailOutliers($renderer, true, $collegeId);
        $name = $this->getOutlierService()->getCollegeName($collegeId);


        $view = new JsonModel(
            array(
                'status' => 'ok',
                'message' => 'Sent to: ' . $name
            )
        );

        return $view;
    }

    protected function getRenderer()
    {
        $renderer = $this->getServiceLocator()
            ->get('Zend\View\Renderer\RendererInterface');

        return $renderer;
    }

    /**
     * @deprecated
     * @return array|\Zend\Http\Response
     */
    public function emailOutliersAction()
    {
        $this->longRunningScript();

        $renderer = $this->getRenderer();

        $outliersService = $this->getOutlierService();

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




    // The following methods are for Maximizing Resources only and should be moved to a class of their own or generalized


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
}
