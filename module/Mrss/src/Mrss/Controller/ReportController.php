<?php

namespace Mrss\Controller;

use Mrss\Form\PeerComparisonDemographics;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Service\Report;
use Mrss\Form\PeerComparison;

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

        $observation = $this->currentObservation($year);
        $reportData = $this->getReportService()->getNationalReportData($observation);

        return array(
            'reportData' => $reportData,
            'college' => $observation->getCollege(),
            'breakpoints' => $this->getReportService()
                    ->getPercentileBreakPointLabels()
        );
    }

    public function peerAction()
    {
        $form = new PeerComparison;

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                //var_dump($data); die;

                // Save to session? we may want this in the db eventually to save
                // peer groups and/or peer criteria
            }
        }

        return array(
            'form' => $form
        );
    }

    public function peerdemographicAction()
    {
        $form = new PeerComparisonDemographics;

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                var_dump($form->getData());
            }
        }

        return array(
            'form' => $form
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
