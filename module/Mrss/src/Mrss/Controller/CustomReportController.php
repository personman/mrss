<?php

namespace Mrss\Controller;

use Mrss\Entity\PeerGroup;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Report as ReportForm;
use Mrss\Entity\Report;
use Mrss\Entity\ReportItem;
use Mrss\Entity\College;
use Zend\View\Model\ViewModel;

class CustomReportController extends ReportController
{
    /**
     * List a college's reports
     *
     * @return array
     */
    public function indexAction()
    {
        //$isAdmin = $this->
        return array(
            'reports' => $this->getReportModel()
                    ->findByCollegeAndStudy($this->currentCollege(), $this->currentStudy())
        );
    }

    public function addAction()
    {
        $form = new ReportForm;

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Report'
            )
        );

        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }
        $report = $this->getReport($id);

        $form->bind($report);

        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getReportModel()->save($report);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Report saved.');

                return $this->redirect()->toRoute(
                    'reports/custom/build',
                    array('id' => $report->getId())
                );
            }

        }


        return array(
            'form' => $form,
            'report' => $report
        );
    }

    public function editAction()
    {
        return $this->addAction();
    }

    public function buildAction()
    {
        $id = $this->params()->fromRoute('id');
        $report = $this->getReport($id);
        $this->populateCache($report);

        return array(
            'report' => $report
        );
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $print = $this->params()->fromRoute('print');
        $report = $this->getReport($id);
        $this->populateCache($report);

        $printMedia = 'print';
        if ($print) {
            $printMedia .= ',screen';

            // For the print version, show all labels
            $this->showAllLabels($report);
        }

        return array(
            'report' => $report,
            'print' => $print,
            'printMedia' => $printMedia
        );
    }

    protected function showAllLabels(Report $report)
    {
        foreach ($report->getItems() as $item) {
            $chart = $item->getCacheChart();

            if ($chart['chart']['type'] == 'column') {
                foreach ($chart['series'] as $key => &$series) {
                    foreach ($series['data'] as $dataKey => &$dataPoint) {
                        if (isset($dataPoint['dataLabels'])) {
                            $dataPoint['dataLabels']['enabled'] = true;
                        }
                    }
                }

            }

            if ($chart['chart']['type'] == 'line') {
                $chart['plotOptions']['line']['dataLabels']['enabled'] = true;
            }

            $item->setCacheChart($chart);

            //prd($chart['series']);
        }
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        $report = $this->getReport($id);

        $name = $report->getName();

        $this->getReportModel()->delete($report);

        $this->flashMessenger()->addSuccessMessage('Report deleted:' . $name);
        return $this->redirect()->toRoute('reports/custom');
    }

    public function populateCache(Report $report)
    {
        $this->longRunningScript();

        /** @var \Mrss\Service\Report\CustomReportBuilder $reportBuilder */
        $reportBuilder = $this->getServiceLocator()->get('service.report.builder');

        $reportBuilder->build($report);
    }

    /**
     * @return \Mrss\Model\Report
     */
    protected function getReportModel()
    {
        return $this->getServiceLocator()->get('model.report');
    }

    /**
     * @return \Mrss\Model\ReportItem
     */
    protected function getReportItemModel()
    {
        return $this->getServiceLocator()->get('model.reportItem');
    }


    /**
     * @param $id
     * @throws \Exception
     * @return Report
     */
    public function getReport($id)
    {
        if (!empty($id)) {
            $report = $this->getReportModel()->find($id);

            $admin = $this->isAllowed('adminMenu', 'view');
            if (!$admin && $report->getCollege()->getId() != $this->currentCollege()->getId()) {
                throw new \Exception('You cannot edit reports that do not belong to your college.');
            }
        }

        if (empty($report)) {
            $report = new Report;
            $report->setCollege($this->currentCollege());
            $report->setStudy($this->currentStudy());
        }

        return $report;
    }

    public function clearCacheAction()
    {
        $studyId = $this->currentStudy()->getId();
        $this->getReportItemModel()->clearCache($studyId);
        $this->getSettingModel()->setValueForIdentifier('custom_report_cache_' . $studyId, date('c'));
        $this->getSettingModel()->getEntityManager()->flush();

        $this->flashMessenger()->addSuccessMessage("Cache cleared.");
        return $this->redirect()->toRoute('reports/custom/admin');
    }

    public function adminAction()
    {
        $studyId = $this->currentStudy()->getId();

        $cacheClearDate = $this->getSettingModel()->getValueForIdentifier('custom_report_cache_' . $studyId);

        $reportsNeedingCache = $this->getReportModel()->findByEmptyCache($studyId);

        return array(
            'cacheClearDate' => $cacheClearDate,
            'reportsNeedingCache' => $reportsNeedingCache
        );
    }

    public function rebuildCacheAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $report = $this->getReport($id);
            $this->populateCache($report);
        }

        // Send back a simple response (no view file, no layout)
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    public function copyAction()
    {
        $this->longRunningScript();
        $start = microtime(true);

        $id = $this->params()->fromRoute('id');
        $report = $this->getReport($id);

        foreach ($this->getAllColleges() as $college) {
            // Skip the current college to prevent dupes
            if ($college->getId() == $this->currentCollege()->getId()) {
                continue;
            }

            $this->copyCustomReport($report, $college);
        }

        $elapsed = round(microtime(true) - $start);
        $this->flashMessenger()->addSuccessMessage(
            "Report copied to all institutions in $elapsed seconds.
            Now <a href='/reports/custom/admin'>rebuild cache</a>."
        );
        return $this->redirect()->toRoute('reports/custom');
    }

    protected function copyCustomReport(Report $sourceReport, $college)
    {
        $peerGroupIdToCopy = 149;
        $peerGroupId = $this->copyPeerGroup($peerGroupIdToCopy, $college);

        $report = new Report;
        $report->setCollege($college);
        $report->setStudy($this->currentStudy());
        $report->setName($sourceReport->getName());
        $report->setDescription($sourceReport->getDescription());
        $report->setSourceReportId($sourceReport->getId());

        $this->getReportModel()->save($report);

        foreach ($sourceReport->getItems() as $reportItem) {
            $this->copyItem($reportItem, $report, $peerGroupId);
        }

        $this->getReportModel()->getEntityManager()->flush();
    }

    protected function getAllColleges()
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');

        return $collegeModel->findByStudy($this->currentStudy());
    }

    /**
     * @param $peerGroupIdToCopy
     * @param College $college
     * @return null
     */
    protected function copyPeerGroup($peerGroupIdToCopy, College $college)
    {
        $newPeerGroupId = null;
        if (!empty($peerGroupIdToCopy)) {
            $sampleGroup = $this->getPeerGroupModel()->find($peerGroupIdToCopy);
            $peers = $sampleGroup->getPeers();

            // Remove the owning college from the peer group
            if (($key = array_search($college->getId(), $peers)) !== false) {
                unset($peers[$key]);
            }

            // Create the new peer group
            $newPeerGroup = new PeerGroup();
            $newPeerGroup->setName($sampleGroup->getName());
            $newPeerGroup->setCollege($college);
            $newPeerGroup->setPeers($peers);

            $this->getPeerGroupModel()->save($newPeerGroup);

            $newPeerGroupId = $newPeerGroup->getId();
        }

        return $newPeerGroupId;
    }

    /**
     * Strip out anything that shouldn't be copied.
     *
     * @param $sourceItem
     * @param $newReport
     * @param $peerGroupId
     */
    protected function copyItem(ReportItem $sourceItem, Report $newReport, $peerGroupId = null)
    {
        $config = $sourceItem->getConfig();
        $config['peerGroup'] = $peerGroupId;

        $item = new ReportItem();
        $item->setReport($newReport);
        $item->setName($sourceItem->getName());
        $item->setSequence($sourceItem->getSequence());
        $item->setType($sourceItem->getType());
        $item->setDescription($sourceItem->getDescription());
        $item->setYear($sourceItem->getYear());
        $item->setConfig($config);
        $item->setHighlightedCollege($sourceItem->getReport()->getCollege());

        $this->getReportItemModel()->save($item);
    }

    /**
     * @return \Mrss\Model\Setting
     */
    public function getSettingModel()
    {
        return $this->getServiceLocator()->get('model.setting');
    }
}
