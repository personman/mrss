<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class BenchmarkController extends AbstractActionController
{
    public function indexAction()
    {
        $benchmarkGroupModel = $this->getServiceLocator()
            ->get('model.benchmarkGroup');

        return array(
            'benchmarkGroups' => $benchmarkGroupModel->findAll()
        );
    }

    public function viewAction()
    {
        $collegeIds = array(780, 782, 819, 884, 873, 931, 932, 1085, 1032);

        $benchmarkModel = $this->getServiceLocator()
            ->get('model.benchmark');
        $benchmark = $benchmarkModel->find($this->params('id'));

        $observationModel = $this->getServiceLocator()
            ->get('model.observation');

        $observations = $observationModel->findForChart(
            $benchmark->getDbColumn(),
            $collegeIds
        );
        $observations = json_encode($observations, JSON_NUMERIC_CHECK);

        return array(
            'benchmark' => $benchmark,
            'observations' => $observations,
            'collegeIds' => $collegeIds
        );
    }
}
