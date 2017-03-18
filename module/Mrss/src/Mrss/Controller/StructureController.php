<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\BenchmarkSelect;
use Zend\View\Model\JsonModel;

class StructureController extends AbstractActionController
{
    public function indexAction()
    {

    }

    public function editAction()
    {
        // @todo: get current subscription so this supports sections
        $subscription = null;
        $benchmarks = $this->currentStudy()->getStructuredBenchmarks(false, 'id', $subscription, false);
        $benchmarkForm = new BenchmarkSelect($benchmarks);

        $structureId = $this->params()->fromRoute('id');
        $structure = $this->getStructureModel()->find($structureId);

        return array(
            'benchmarkForm' => $benchmarkForm,
            'structure' => $structure
        );
    }

    public function saveAction()
    {
        $structureId = $this->params()->fromPost('structureId');
        $json = $this->params()->fromPost('json');
        $structure = $this->getStructureModel()->find($structureId);
        $structure->setJson($json);

        $this->getStructureModel()->save($structure);
        $this->getStructureModel()->getEntityManager()->flush();

        $viewModel = new JsonModel(
            array(
                'status' => 'ok'
            )
        );

        return $viewModel;
    }

    /**
     * @return \Mrss\Model\Structure
     */
    protected function getStructureModel()
    {
        return $this->getServiceLocator()->get('model.structure');
    }
}
