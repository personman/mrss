<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\View\Http\ViewManager;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class ObservationController extends AbstractActionController
{
    public function viewAction()
    {
        $observationId = $this->params('id');
        $ObservationModel = $this->getServiceLocator()->get('model.observation');

        return array(
            'observation' => $ObservationModel->find($observationId),
            'fields' => $this->getFields()
        );
    }

    public function editAction()
    {
        $observationId = $this->params('id');
        $benchmarkGroupId = 1;

        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        $observation = $ObservationModel->find($observationId);

        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmarkGroup')
            ->find($benchmarkGroupId);

        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');
        $form = $formService->buildForm($benchmarkGroup);

        $form->setAttribute('class', 'form-horizontal');

        // @todo: bind observation to form
        $form->bind($observation);

        return array(
            'form' => $form,
            'observation' => $observation
        );
    }

    /**
     * Get field metadata from the benchmark entity
     *
     * @return array
     */
    protected function getFields()
    {
        $benchmarkModel = $this->getServiceLocator()->get('model.benchmark');

        $benchmarks = $benchmarkModel->findAll();
        $fields = array();
        foreach ($benchmarks as $benchmark) {
            $fields[$benchmark->getDbColumn()] = $benchmark->getName();
        }

        return $fields;
    }
}
