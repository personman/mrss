<?php

namespace Mrss\Controller;

use Mrss\Entity\ReportItem;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Entity\Chart;
use Mrss\Form\Explore;

class ReportItemController extends CustomReportController
{
    /** @var  \Mrss\Entity\Report */
    protected $report;

    public function addAction()
    {
        $this->longRunningScript();

        $id = $this->params('id');
        $report = $this->getReport($id);
        $this->report = $report;

        $edit = false;

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        $benchmarks = $this->getBenchmarks();

        $colleges = array();

        $years = $this->getSubscriptionModel()->getYearsWithReports($study, $this->checkReportAccess());
        $peerGroups = $this->getPeerGroups();
        
        $form = new Explore($benchmarks, $colleges, $years, $peerGroups);

        // Are we editing an existing report item?
        $item = null;
        $item_id = $this->params()->fromRoute('item_id');
        if ($item_id) {
            $item = $this->getReportItemModel()->find($item_id);
            if ($item) {
                $data = $item->getConfig(true);
                $data['buttons']['submit'] = 'Save';
                $data['buttons']['preview'] = 'Preview';

                $form->setData($data);

                $year = $data['year'];
                $edit = true;
            }
        }


        $chart = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();

            /*if (!empty($post['id'])) {
                $post = $this->getChartModel()->find($post['id'])->getConfig();
                $post['buttons']['submit'] = null;
            }*/

            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();
                $year = $data['year'];

                $chart = $this->getReportService()
                    ->setObservation($this->currentObservation())
                    ->getChart($data, $year);

                // Save it, if requested
                if (!empty($data['buttons']['submit'])) {
                    if (!empty($data['title'])) {
                        $this->saveItem($data, $chart, $item);
                        $this->flashMessenger()->addSuccessMessage("Saved.");
                        return $this->redirect()->toRoute('reports/custom/build', array('id' => $report->getId()));
                    } else {
                        $this->flashMessenger()
                            ->addErrorMessage("You must enter a title to save.");
                    }
                }
            }

            // Restore the button labels
            $post['buttons']['submit'] = 'Save';
            $post['buttons']['preview'] = 'Preview';
            $form->setData($post);
        } else {
            if (!empty($data)) {
                $year = $data['year'];
                $chart = $this->getReportService()
                    ->setObservation($this->currentObservation())
                    ->getChart($data, $year);
            }
        }

        if (empty($year)) {
            $year = $data['year'];
        }

        return array(
            'form' => $form,
            'chart' => $chart,
            'year' => $year,
            'report' => $report,
            'edit' => $edit
        );
    }

    /*public function editAction()
    {
        return $this->addAction();
    }*/

    public function reorderAction()
    {
        $id = $this->params()->fromRoute('id');
        $data = array_flip($this->params()->fromPost('item'));

        $report = $this->getReport($id);
        foreach ($report->getItems() as $item) {
            if (isset($data[$item->getId()])) {
                $item->setSequence($data[$item->getId()]);
                $this->getReportItemModel()->save($item);
            }
        }

        $this->getReportItemModel()->getEntityManager()->flush();
        die('ok');
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $item_id = $this->params()->fromRoute('item_id');

        $report = $this->getReport($id);

        foreach ($report->getItems() as $item) {
            if ($item->getId() == $item_id) {
                $this->getReportItemModel()->delete($item);
                $this->flashMessenger()->addSuccessMessage('Item deleted.');
            }
        }

        return $this->redirect()->toRoute('reports/custom/build', array('id' => $report->getId()));
    }

    protected function saveItem($config, $chart, $item = null)
    {
        $model = $this->getReportItemModel();
        $name = $config['title'];
        $type = $config['presentation'];
        $description = $config['content'];


        // First, see if we're updating a chart with the same name
        //$item = $model->findByStudyCollegeAndName($study, $college, $name);

        // If not, create a chart entity
        if (empty($item)) {
            $item = new ReportItem();
            $item->setReport($this->report);
            $item->setName($name);
            $item->setSequence($this->getNextSequence());
        }

        // Apply the updates
        $item->setType($type);
        $item->setConfig($config);
        $item->setDescription($description);
        $item->setCache(json_encode($chart));
        if (!empty($config['year'])) {
            $item->setYear($config['year']);
        }

        // Save it and flush
        $model->save($item);
        $model->getEntityManager()->flush();
    }

    protected function getPeerGroups()
    {
        $peerGroups = array();
        foreach ($this->currentCollege()->getPeerGroups() as $group) {
            $count = count($group->getPeers());
            $name = $group->getName();
            $peerGroups[$group->getId()] = "$name ($count)";
        }

        return $peerGroups;
    }
    
    protected function getBenchmarks()
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
                // Skip non-report benchmarks
                if (!$benchmark->getIncludeInNationalReport()) {
                    continue;
                }
                $group['options'][$benchmark->getDbColumn()] = $benchmark->getDescriptiveReportLabel();
            }

            $benchmarks[$benchmarkGroup->getId()] = $group;
        }
        
        return $benchmarks;
    }

    protected function getNextSequence()
    {
        $sequence = 0;
        $items = $this->report->getItems();
        foreach ($items as $item) {
            $sequence = $item->getSequence();
        }

        $sequence++;

        return $sequence;
    }
}
