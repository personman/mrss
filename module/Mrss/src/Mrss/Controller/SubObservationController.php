<?php


namespace Mrss\Controller;

use Mrss\Entity\SubObservation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class SubObservationController extends AbstractActionController
{
    protected $subObservationModel;

    protected $observationModel;

    /**
     * @return \Mrss\Model\SubObservation
     */
    public function getSubObservationModel()
    {
        if (empty($this->subObservationModel)) {
            $this->subObservationModel = $this->getServiceLocator()
                ->get('model.subobservation');
        }

        return $this->subObservationModel;
    }

    /**
     * @return \Mrss\Model\Observation
     */
    public function getObservationModel()
    {
        if (empty($this->observationModel)) {
            $this->observationModel = $this->getServiceLocator()
                ->get('model.observation');
        }

        return $this->observationModel;

    }

    public function editAction()
    {
        // Fetch the benchmarkGroup
        $benchmarkGroupId = $this->params('benchmarkGroup');
        $subObId = $this->params('subId');

        $disabled = !$this->currentStudy()->getDataEntryOpen();

        /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */
        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmark.group')
            ->find($benchmarkGroupId);

        if (empty($benchmarkGroup)) {
            throw new \Exception('Benchmark group not found');
        }

        // We'll need the observation
        $observation = $this->currentObservation();

        // Now get the form
        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');
        $form = $formService->buildForm(
            $benchmarkGroup,
            $observation->getYear(),
            $disabled
        );

        // Fetch or create the subobservation
        $subObservation = $this->getSubObservationModel()->find($subObId);

        if (empty($subObservation)) {
            $subObservation = new SubObservation();
        }

        // Clone for audit trail comparison
        $oldSubObservation = clone $subObservation;

        // Bind it to the form
        $form->bind($subObservation);



        // Process the form
        // Handle form submission
        if ($this->getRequest()->isPost()) {
            if ($disabled) {
                $this->flashMessenger()->addErrorMessage('Data entry is closed.');
                return $this->redirect()->toRoute('data-entry');
            }

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            //var_dump($form->getElements()); die;
            if ($form->isValid()) {
                $subObservation->setObservation($observation);
                $this->getSubObservationModel()->save($subObservation);

                // Calculate sub-ob computed fields and all other computed fields
                $this->getServiceLocator()->get('computedFields')
                    ->calculateAllForSubObservation($subObservation, $benchmarkGroup)
                    ->calculateAllForObservation($subObservation->getObservation());

                // Log changes
                /** @var \Mrss\Service\ObservationAudit $observationAudit */
                $observationAudit = $this->getServiceLocator()
                    ->get('service.observationAudit');
                $observationAudit->logSubObservationChanges(
                    $oldSubObservation,
                    $subObservation,
                    'dataEntry'
                );

                // Merge to the observation and log changes
                $observation = $subObservation->getObservation();
                $oldObservation = clone $observation;
                $observation->mergeSubobservations();
                $this->getObservationModel()->save($observation);

                $observationAudit->logChanges(
                    $oldObservation,
                    $observation,
                    'dataEntry'
                );

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Data saved.');
                return $this->redirect()->toRoute(
                    'data-entry/edit',
                    array('benchmarkGroup' => $benchmarkGroup->getUrl())
                );
            }

        }


        $view = new ViewModel(
            array(
                'form' => $form,
                'observation' => $observation,
                'benchmarkGroup' => $benchmarkGroup
            )
        );

        $this->checkForCustomTemplate($benchmarkGroup, $view);

        return $view;
    }

    public function deleteAction()
    {
        // Load the subob
        $subObId = $this->params('subId');
        $benchmarkGroupId = $this->params('benchmarkGroup');
        /** @var SubObservation $subObservation */
        $subObservation = $this->getSubObservationModel()->find($subObId);

        // Let's make sure they have permission to delete this
        $college = $subObservation->getObservation()->getCollege()->getId();
        $currentCollege = $this->currentCollege()->getId();

        if ($college != $currentCollege) {
            throw new \Exception('You do not have permission to delete that.');
        }

        // Actually delete it
        $this->getSubObservationModel()->delete($subObservation);

        // Redirect and show a message
        $this->flashMessenger()->addSuccessMessage('Academic division deleted.');
        return $this->redirect()->toRoute(
            'data-entry/edit',
            array(
                'benchmarkGroup' => $benchmarkGroupId
            )
        );
    }

    public function checkAction()
    {
        $benchmarkGroupId = $this->params()->fromRoute('benchmarkGroup');

        /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */
        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmark.group')
            ->find($benchmarkGroupId);

        // We'll need the observation
        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->currentObservation();

        $subObservations = $observation->getSubObservations();

        $totals = array(
            'inst_cost_full_expend' => 0,
            'inst_cost_full_num' => 0,
            'inst_cost_full_cred_hr' => 0,
            'inst_cost_part_expend' => 0,
            'inst_cost_part_num' => 0,
            'inst_cost_part_cred_hr' => 0,
        );

        $form1to2map = array(
            'inst_cost_full_expend' => 'inst_full_expend',
            'inst_cost_full_num' => 'inst_full_num',
            'inst_cost_full_cred_hr' => 'inst_full_cred_hrs',
            'inst_cost_part_expend' => 'inst_part_expend',
            'inst_cost_part_num' => 'inst_part_num',
            'inst_cost_part_cred_hr' => 'inst_part_cred_hrs',
        );

        // Calculate totals
        foreach ($subObservations as $subOb) {
            foreach ($totals as $key => $total) {
                $value = $subOb->get($key);
                $totals[$key] += $value;
            }
        }

        // Load up the benchmarks
        $benchmarks = array();
        foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
            $benchmarks[$benchmark->getDbColumn()] = $benchmark;
        }

        return array(
            'subObservations' => $subObservations,
            'totals' => $totals,
            'observation' => $observation,
            'benchmarkGroup' => $benchmarkGroup,
            'benchmarks' => $benchmarks,
            'benchmarkMap' => $form1to2map
        );
    }

    /**
     * If a custom template is set up for this benchmarkGroup, switch to it.
     * Get the id and file name from config (differs in production)
     */
    public function checkForCustomTemplate($benchmarkGroup, $view)
    {
        $config = $this->getServiceLocator()->get('Config');

        $id = $benchmarkGroup->getId();

        if (!empty($config['subobservation_templates'][$id])) {
            $template = $config['subobservation_templates'][$id];
            $view->setTemplate('mrss/sub-observation/' . $template);
        }
    }
}
