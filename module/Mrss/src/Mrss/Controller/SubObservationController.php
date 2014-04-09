<?php


namespace Mrss\Controller;

use Mrss\Entity\SubObservation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class SubObservationController extends AbstractActionController
{
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
        $subObModel = $this->getServiceLocator()
            ->get('model.subobservation');
        $subObservation = $subObModel->find($subObId);

        if (empty($subObservation)) {
            $subObservation = new SubObservation();
        }

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
                $subObModel->save($subObservation);
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
