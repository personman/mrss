<?php


namespace Mrss\Controller;

use Mrss\Entity\SubObservation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class SubObservationController extends AbstractActionController
{
    protected $subObservationModel;

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

    public function editAction()
    {
        // Fetch the benchmarkGroup
        $benchmarkGroupId = $this->params('benchmarkGroup');
        $subObId = $this->params('subId');

        /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */
        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmarkGroup')
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
            $observation->getYear()
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

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            //var_dump($form->getElements()); die;
            if ($form->isValid()) {
                $subObservation->setObservation($observation);
                $this->getSubObservationModel()->save($subObservation);

                // Log changes
                $this->getServiceLocator()->get('service.observationAudit')
                    ->logSubObservationChanges($oldSubObservation, $subObservation, 'dataEntry');

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Data saved.');
                return $this->redirect()->toRoute(
                    'data-entry/edit',
                    array('benchmarkGroup' => $benchmarkGroup->getId())
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
        $this->flashMessenger()->addSuccessMessage('Academic unit deleted.');
        return $this->redirect()->toRoute(
            'data-entry/edit',
            array(
                'benchmarkGroup' => $benchmarkGroupId
            )
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
