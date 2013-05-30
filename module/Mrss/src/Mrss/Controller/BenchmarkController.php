<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Entity\Benchmark;
use Mrss\Form;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;
use Zend\View\Model\ViewModel;

class BenchmarkController extends AbstractActionController
{
    protected $benchmarkModel;

    public function indexAction()
    {
        $studyId = $this->params()->fromRoute('study');
        $studyModel = $this->getServiceLocator()
            ->get('model.study');
        $study = $studyModel->find($studyId);
        $benchmarkGroups = $study->getBenchmarkGroups();

        //$benchmarkGroupModel = $this->getServiceLocator()
        //    ->get('model.benchmarkGroup');

        return array(
            //'benchmarkGroups' => $benchmarkGroupModel->findAll(),
            'benchmarkGroups' => $benchmarkGroups,
            'study' => $study,
            'yearsToShow' => range(2007, date('Y'))
        );
    }

    public function viewAction()
    {
        $collegeIds = array(95, 96, 97, 98, 99, 100, 101, 102, 103);

        $benchmark = $this->getBenchmarkModel()->find($this->params('id'));

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

    public function addAction()
    {
        $benchmarkGroupId = $this->params('benchmarkGroup');
        $benchmarkGroupModel = $this->getServiceLocator()
            ->get('model.benchmarkGroup');

        $benchmarkGroup = $benchmarkGroupModel->find($benchmarkGroupId);

        if (empty($benchmarkGroup)) {
            throw new \Exception('Benchmark group not found.');
        }

        $benchmark = new Benchmark;
        $benchmark->setBenchmarkGroup($benchmarkGroup);

        // Get the form
        $form = $this->getBenchmarkForm($benchmark);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getBenchmarkModel()->save($benchmark);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Benchmark saved.');
                return $this->redirect()->toRoute(
                    'benchmarks',
                    array('study' => $benchmarkGroup->getStudy()->getId())
                );
            }

        }

        return array(
            'form' => $form,
            'benchmarkGroup' => $benchmarkGroup
        );
    }

    public function editAction()
    {
        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $benchmark = $this->getBenchmarkModel()->find($id);

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
                $this->getBenchmarkModel()->save($benchmark);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Benchmark saved.');
                return $this->redirect()->toRoute(
                    'benchmarks',
                    array('study' => $benchmark->getBenchmarkGroup()
                        ->getStudy()->getId())
                );
            }

        }

        return array(
            'form' => $form
        );
    }

    /**
     * Show a list of benchmarks that can be added to the equation
     *
     * @return ViewModel
     */
    public function equationAction()
    {
        // Get the studies
        $studies = $this->getServiceLocator()->get('model.study')->findAll();

        $viewModel = new ViewModel(
            array(
                'studies' => $studies
            )
        );
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    /**
     * Get the form and bind the entity
     *
     * @param BenchmarkEntity $benchmark
     * @return \Zend\Form\Form
     */
    public function getBenchmarkForm(BenchmarkEntity $benchmark)
    {
        // Inject the equation validator
        $em = $this->getServiceLocator()->get('em');
        $benchmark->setEquationValidator(
            $this->getServiceLocator()->get('validator.equation')
        );

        // Build form
        $form = new Form\Benchmark;

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\Benchmark'));
        $form->bind($benchmark);

        return $form;
    }

    public function setBenchmarkModel(\Mrss\Model\Benchmark $model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        if (empty($this->benchmarkModel)) {
            $this->benchmarkModel = $this->getServiceLocator()
                ->get('model.benchmark');
        }

        return $this->benchmarkModel;
    }
}
