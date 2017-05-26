<?php

namespace Mrss\Controller;

use Mrss\Entity\ReportItem;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Entity\Chart;
use Mrss\Form\Explore;
use Zend\View\Model\ViewModel;

class ReportItemController extends CustomReportController
{
    /** @var  \Mrss\Entity\Report */
    protected $report;

    public function addAction()
    {
        $this->longRunningScript();

        $footnotes = array();

        $reportId = $this->params('id');
        $report = $this->getReport($reportId);
        $this->report = $report;

        $form = $this->getForm();

        $edit = false;
        if ($item = $this->getItem()) {
            $data = $item->getConfig(true);
            $year = $data['year'];
            $edit = true;
        }

        $chart = null;
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                //pr($data);

                if (!empty($data['multiTrend']) && $data['multiTrend'] == 'false') {
                    $data['multiTrend'] = 0;
                }

                // Add system to config
                $systemId = null;
                if ($system = $report->getSystem()) {
                    $systemId = $system->getId();
                }
                $data['system'] = $systemId;

                // What type of button was pressed?
                $buttonPressed = $this->getButtonPressed($data);

                // Handle cancel
                if ($buttonPressed == 'cancel') {
                    return $this->redirect()->toRoute('reports/custom/build', array('id' => $report->getId()));
                }

                $chartBuilder = $this->getReportService()
                    ->getChartBuilder($data);

                $chart = $chartBuilder->getChart();
                $footnotes = $chartBuilder->getFootnotes();

                // Save it, if requested
                if ($buttonPressed == 'save') {
                    if (!empty($data['title']) || $data['presentation'] == 'text') {
                        $this->saveItem($data, $chart, $footnotes, $item);
                        $this->flashMessenger()->addSuccessMessage("Saved.");
                        return $this->redirect()->toRoute('reports/custom/build', array('id' => $report->getId()));
                    } else {
                        $this->flashMessenger()
                            ->addErrorMessage("You must enter a title to save.");
                    }
                }
            }

            // Restore the button labels
            $post = $this->restoreButtonLabels($post);
            $form->setData($post);
        } elseif (isset($data)) {
            $builder = $this->getChartBuilder($data);
            $chart = $builder->getChart();
            $footnotes = $builder->getFootnotes();
        } else {
            if ($presentationType = $this->params()->fromRoute('type')) {
                $form->get('presentation')->setValue($presentationType);
            }
        }

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        // Reset button proxy hidden fields
        $form->get('isCancel')->setValue(false);
        $form->get('isPreview')->setValue(false);

        // Substitute variables (years)
        $footnotes = $this->subFootnoteVariables($footnotes, $year);

        $viewModel = new ViewModel(array(
            'form' => $form,
            'chart' => $chart,
            'footnotes' => $footnotes,
            'report' => $report,
            'edit' => $edit,
            'defaultBreakpoints' => $this->getReportService()->getPercentileBreakpointsForStudy(),
            'benchmarksByInputType' => $this->currentStudy()->getBenchmarksByInputType()
        ));

        return $viewModel;
    }

    public function getForm()
    {
        $benchmarks = $this->getBenchmarks();
        $colleges = array();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $years = $this->getSubscriptionModel()->getYearsWithReports($study, $this->currentCollege());
        $peerGroups = $this->getPeerGroups();
        $includeTrends = $this->getIncludeTrends();
        $allBreakpoints = $this->getReportService()->getPercentileBreakpoints();

        $systems = null;
        if ($this->getStudyConfig()->use_structures) {
            $systems = $this->currentCollege()->getSystems();
        }


        $form = new Explore(
            $benchmarks,
            $colleges,
            $years,
            $peerGroups,
            $includeTrends,
            $allBreakpoints,
            $systems,
            $this->getStudyConfig()
        );

        // Are we editing an existing report item?
        if ($item = $this->getItem()) {
            $data = $item->getConfig(true);
            $data = $this->restoreButtonLabels($data);
            $form->setData($data);
        }

        return $form;
    }

    protected function restoreButtonLabels($data)
    {
        $data['buttons']['submit'] = 'Save';
        $data['buttons']['preview'] = 'Preview';
        $data['buttons']['cancel'] = 'Cancel';

        return $data;
    }

    public function getItem()
    {
        // Are we editing an existing report item?
        $item = null;
        $item_id = $this->params()->fromRoute('item_id');
        if ($item_id) {
            $item = $this->getReportItemModel()->find($item_id);
        }

        return $item;
    }

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

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');

        return $response;
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

    protected function saveItem($config, $chart, $footnotes, $item = null)
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
            $item->setSequence($this->getNextSequence());
        }

        // Apply the updates
        $item->setName($name);
        $item->setType($type);
        $item->setConfig($config);
        $item->setDescription($description);

        $cache = array(
            'chart' => $chart,
            'footnotes' => $footnotes
        );
        $item->setCache($cache);
        if (!empty($config['year'])) {
            $item->setYear($config['year']);
        }

        // Save it and flush
        $model->save($item);
        $model->getEntityManager()->flush();
    }

    protected function getPeerGroups()
    {
        $model = $this->getPeerGroupModel();
        $currentUser = $this->zfcUserAuthentication()->getIdentity();
        $groups = $model->findByUserAndStudy($currentUser, $this->currentStudy());

        $peerGroups = array();
        foreach ($groups as $group) {
            $count = count($group->getPeers());
            $name = $group->getName();
            $peerGroups[$group->getId()] = "$name ($count)";
        }

        return $peerGroups;
    }

    protected function getStructureBenchmarks()
    {


    }

    /**
     * @return \Mrss\Entity\College
     */
    protected function getCollege()
    {
        $college = $this->currentCollege();

        return $college;
    }

    protected function getBenchmarks()
    {
        /*if ($this->getStudyConfig()->use_structures) {
            return $this->getStructureBenchmarks();
        }*/

        $space = "&u#160;";
        //$space = "\xA0";
        //$space = "&nbsp;";
        //$space = "\u0020";
        $space = "<span class='indentOption'>&nbsp;&nbsp;&nbsp;</span>";
        $space = "";

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        //$subscription = $this->getSubscriptionByYear($this->currentStudy()->getCurrentYear());

        //$benchmarks = $study->getStructuredBenchmarks(true, 'dbColumn', $subscription);

        $benchmarks = array();
        $subscription = $this->currentObservation()->getSubscription();
        $system = $this->report->getSystem();
        foreach ($this->getAllBenchmarkGroups($subscription, $system) as $benchmarkGroup) {
            $groupChildren = array();

            foreach ($benchmarkGroup->getChildren() as $benchmark) {
                if (get_class($benchmark) == 'Mrss\Entity\BenchmarkHeading') {
                    /** @var \Mrss\Entity\BenchmarkHeading $heading */
                    $heading = $benchmark;
                    $groupChildren[] = array(
                        'disabled' => true,
                        'label' => $this->getVariableSubstitutionService()->substitute($heading->getName()),
                        'value' => null
                    );
                    continue;
                }

                $key = $benchmark->getDbColumn();

                $groupChildren[] = array(
                    'disabled' => false,
                    'label' => $space . $benchmark->getReportLabel(),
                    'value' => $key
                );
            }

            $benchmarkGroupInfo = array(
                'label' => $benchmarkGroup->getName(),
                'options' => $groupChildren
            );

            //pr($benchmarkGroup->getName());

            $benchmarks[] = $benchmarkGroupInfo;
        }


        return $benchmarks;
    }

    /**
     * @return \Mrss\Service\VariableSubstitution
     */
    public function getVariableSubstitutionService()
    {
        return $this->getServiceLocator()->get('service.variableSubstitution');
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

    protected function subFootnoteVariables($footnotes, $year)
    {
        $substitution = $this->getReportService()->getVariableSubstitution()->setStudyYear($year);
        $finalFootnotes = array();
        foreach ($footnotes as $key => $footnote) {
            $finalFootnotes[$key] = $substitution->substitute($footnote);
        }
        $footnotes = $finalFootnotes;

        return $footnotes;
    }

    protected function getChartBuilder($data)
    {
        /** @var \Mrss\Service\Report\ChartBuilder $builder */
        $builder = $this->getReportService()->getChartBuilder($data);
        $builder->setCollege($this->currentCollege());

        return $builder;
    }

    protected function getButtonPressed($data)
    {
        $buttonPressed = 'save';
        if (!empty($data['isCancel'])) {
            $buttonPressed = 'cancel';
        } elseif (!empty($data['isPreview'])) {
            $buttonPressed = 'preview';
        }

        return $buttonPressed;
    }
}
