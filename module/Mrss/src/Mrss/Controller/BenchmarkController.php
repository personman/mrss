<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Form;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;

class BenchmarkController extends AbstractActionController
{
    public function indexAction()
    {
        $benchmarkGroupModel = $this->getServiceLocator()
            ->get('model.benchmarkGroup');

        return array(
            'benchmarkGroups' => $benchmarkGroupModel->findAll(),
            'yearsToShow' => range(2007, date('Y'))
        );
    }

    public function viewAction()
    {
        $collegeIds = array(95, 96, 97, 98, 99, 100, 101, 102, 103);

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

    public function editAction()
    {
        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $benchmarkModel = $this->getServiceLocator()
            ->get('model.benchmark');
        $benchmark = $benchmarkModel->find($id);

        // Don't proceed if the benchmark isn't found
        if (empty($benchmark)) {
            throw new \Exception('Benchmark not found.');
        }

        // Get the form
        $form = $this->getBenchmarkForm($benchmark);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $benchmarkModel->save($benchmark);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Benchmark saved.');
                return $this->redirect()->toRoute(
                    'general',
                    array(
                        'controller' => 'benchmarks',
                        'action' => 'index'
                    )
                );
            }

        }

        return array(
            'form' => $form
        );
    }

    /**
     * Build a form from annotations. Bind the passed BenchmarkEntity to the form.
     *
     * @param BenchmarkEntity $benchmark
     * @return \Zend\Form\Form
     */
    public function getBenchmarkForm(BenchmarkEntity $benchmark)
    {
        $em = $this->getServiceLocator()->get('em');

        // Build form
        $form = new Form\Benchmark;

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\Benchmark'));
        $form->bind($benchmark);

        return $form;
    }
}
