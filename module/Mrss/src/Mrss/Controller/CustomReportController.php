<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Report as ReportForm;
use Mrss\Entity\Report;

class CustomReportController extends ReportController
{
    /**
     * List a college's reports
     *
     * @return array
     */
    public function indexAction()
    {
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
            'form' => $form
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
        $changed = false;
        foreach ($report->getItems() as $item) {
            if (null == $item->getCache()) {
                $this->longRunningScript();

                $chart = $this->getReportService()
                    ->setObservation($this->currentObservation())
                    ->getChart($item->getConfig(), $item->getYear());

                $item->setCache($chart);
                $this->getReportItemModel()->save($item);
                $changed = true;
            }
        }

        if ($changed) {
            $this->getReportItemModel()->getEntityManager()->flush();
        }
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

            if ($report->getCollege()->getId() != $this->currentCollege()->getId()) {
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

}
