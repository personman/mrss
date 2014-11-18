<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Entity\Benchmark;
use Mrss\Model\Observation as ObservationModel;
use Mrss\Model\College as CollegeModel;
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

        $years = $this->getServiceLocator()->get('model.subscription')
            ->getYearsWithSubscriptions($study);
        rsort($years);

        // Sparklines
        $observationModel = $this->getServiceLocator()->get('model.observation');
        $sparklines = array();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                $data = $observationModel
                    ->getSparkline($benchmark, $this->currentCollege());
                $asString = implode(',', $data);
                $sparklines[$benchmark->getId()] = $asString;
            }
        }

        return array(
            //'benchmarkGroups' => $benchmarkGroupModel->findAll(),
            'benchmarkGroups' => $benchmarkGroups,
            'study' => $study,
            'yearsToShow' => $years,
            'sparklines' => $sparklines,
            'activeCollege' => $this->currentCollege()
        );
    }

    public function viewAction()
    {
        $collegeIds = array(95, 96, 97, 98, 99, 100, 101, 102, 103);

        $benchmark = $this->getBenchmarkModel()->find($this->params('id'));

        /** @var ObservationModel $observationModel */
        $observationModel = $this->getServiceLocator()
            ->get('model.observation');

        $observations = $observationModel->findForChart(
            $benchmark->getDbColumn(),
            $collegeIds
        );
        $observations = json_encode($observations, JSON_NUMERIC_CHECK);

        // Get the college names
        /** @var CollegeModel $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $colleges = array();
        foreach ($collegeIds as $collegeId) {
            $colleges[] = $collegeModel->find($collegeId);
        }

        return array(
            'benchmark' => $benchmark,
            'observations' => $observations,
            'colleges' => $colleges
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
                    'benchmark',
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

                // Check equation
                /** @var \Mrss\Service\ComputedFields $computedFields */
                $computedFields = $this->getServiceLocator()->get('computedFields');
                $equationOk = $computedFields
                    ->checkEquation($benchmark->getEquation());
                if (!$equationOk) {
                    $error = $computedFields->getError();
                    $dbColumn = $benchmark->getDbColumn();
                    $error = "Error in equation for $dbColumn: $error<br>";
                    $this->flashMessenger()->addErrorMessage($error);
                }


                return $this->redirect()->toRoute(
                    'benchmark',
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

    public function reorderAction()
    {
        $benchmarkGroupId = $this->params()->fromPost('benchmarkGroupId');
        $newBenchmarkSequences = $this->params()->fromPost('benchmarks');
        $newBenchmarkHeadingSequences = $this->params()
            ->fromPost('headings');
        $newBenchmarkSequences = array_flip($newBenchmarkSequences);
        $newBenchmarkHeadingSequences = array_flip($newBenchmarkHeadingSequences);

        //pr($newBenchmarkSequences);
        //pr($newBenchmarkHeadingSequences);

        /** @var \Mrss\Model\BenchmarkGroup $benchmarkGroupModel */
        $benchmarkGroupModel = $this->getServiceLocator()
            ->get('model.benchmarkGroup');

        $benchmarkGroup = $benchmarkGroupModel->find($benchmarkGroupId);

        if (!empty($benchmarkGroup)) {
            $benchmarks = $benchmarkGroup->getBenchmarks();

            foreach ($benchmarks as $benchmark) {
                if (isset($newBenchmarkSequences[$benchmark->getId()])) {
                    $benchmark->setSequence(
                        $newBenchmarkSequences[$benchmark->getId()]
                    );
                }
            }

            $headings = $benchmarkGroup->getBenchmarkHeadings();
            foreach ($headings as $heading) {
                if (isset($newBenchmarkHeadingSequences[$heading->getId()])) {
                    $heading->setSequence(
                        $newBenchmarkHeadingSequences[$heading->getId()]
                    );
                }
            }

            $em = $this->getServiceLocator()->get('em');
            $em->flush();
        }

        //print_r($benchmarkGroupId); print_r($benchmarks); die;

        echo 'ok';
        die;
    }

    public function equationsAction()
    {
        $benchmarks = $this->getBenchmarkModel()
            ->findEmptyEquations($this->currentStudy());
            //->findComputed($this->currentStudy());

        // Load the NCCBP equations
        $db = $this->getServiceLocator()->get('nccbp-db');
        $query = "select type, field_name, global_settings from content_node_field where type = 'computed'";
        $statement = $db->query($query);
        $result = $statement->execute();

        $equations = array();
        foreach ($result as $row) {
            $eq = unserialize($row['global_settings']);
            $eq = $eq['code'];

            $pattern = '/field_(\d+)[a-z]?_/';
            $eq = preg_replace($pattern, '', $eq);

            $importNccbp = $this->getServiceLocator()->get('import.nccbp');

            try {
                if (stristr($row['field_name'], 'field_17b_')) {
                    $field_name = str_replace(
                        'field_17b_',
                        'form17b_dist_learn_grad_',
                        $row['field_name']
                    );
                } else {
                    $field_name = $importNccbp
                        ->convertFieldName($row['field_name'], $row['type'], false);
                }
            } catch (\Exception $e) {
                continue;
            }

            //$field_name =
            $equations[$field_name] = $eq;
        }
        //pr($equations);



        $form = new \Mrss\Form\AbstractForm('equations');

        foreach ($benchmarks as $benchmark) {
            $form->add(
                array(
                    'name' => 'equation[' . $benchmark->getId() . ']',
                    'type' => 'Text',
                    'options' => array(
                        'label' => $benchmark->getReportLabel(),
                        'help-block' => '<pre>' . $equations[$benchmark->getDbColumn()] . '</pre>'
                    ),
                    'attributes' => array(
                        'rows' => 8,
                        'value' => $benchmark->getEquation()
                    )
                )
            );
        }

        $form->add($form->getButtonFieldset());

        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                foreach ($_POST['equation'] as $id => $equation) {
                    $benchmark = $this->getBenchmarkModel()->find($id);
                    $benchmark->setEquation($equation);
                    $this->getServiceLocator()->get('em')->flush();
                }

                $this->flashMessenger()->addSuccessMessage('Saved');
                $this->redirect()->toUrl('/benchmark/equations');
            }
        }

        return array(
            'form' => $form,
            'count' => count($benchmarks)
        );
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

        // Pass in the entity manager for checking uniqueness
        $benchmark->setEntityManager($em);

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
