<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Service\Report;

class ReportController extends AbstractActionController
{
    /**
     * @var Report
     */
    protected $reportService;

    public function calculateAction()
    {
        $years = $this->getReportService()->getYearsWithSubscriptions();
        $yearToPrepare = $this->params()->fromRoute('year');

        if (!empty($yearToPrepare)) {
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
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        $observation = $this->
        $reportData = $this->getReportService()->getNationalReportData($year);

        return array(
            'reportData' => $reportData
        );
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
