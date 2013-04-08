<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class ObservationController extends AbstractActionController
{
    public function viewAction()
    {
        $ObservationModel = $this->getServiceLocator()->get('model.observation');

        return array(
            'observation' => $ObservationModel->find($this->params('id')),
            'fields' => $this->getFields()
        );
    }

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
