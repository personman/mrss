<?php

namespace Mrss\Controller;

use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Entity\Benchmark;
use Mrss\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class BenchmarkController extends BaseController
{
    protected $benchmarkModel;

    public function indexAction()
    {
        takeYourTime();

        $studyConfig = $this->getServiceLocator()->get('study');
        $showHeatMap = $studyConfig->benchmark_completion_heatmap;

        $studyId = $this->params()->fromRoute('study', null);
        if (empty($studyId)) {
            $studyId = $this->currentStudy()->getId();
        }

        /** @var \Mrss\Model\Study $studyModel */
        $studyModel = $this->getServiceLocator()
            ->get('model.study');
        $study = $studyModel->find($studyId);
        $benchmarkGroups = $study->getBenchmarkGroups();


        if ($showHeatMap) {
            $years = $this->getServiceLocator()->get('model.subscription')
                ->getYearsWithSubscriptions($study);
            rsort($years);
        } else {
            $years = array();
        }


        // Are we organizing the benchmarks for data-entry or reports?
        $user = $this->zfcUserAuthentication()->getIdentity();
        $organization = $user->getAdminBenchmarkSorting();

        // Sparklines
        // @todo: use study-wide median for sparkline, if any. Disabled for now.
        //$sparklines = array();
        $counts = array('benchmarks' => 0, 'collected' => 0, 'computed' => 0);
        foreach ($benchmarkGroups as $benchmarkGroup) {
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                /*$data = $observationModel
                    ->getSparkline($benchmark, $this->currentCollege());
                $asString = implode(',', $data);
                $sparklines[$benchmark->getId()] = $asString;*/

                // Counts
                $counts['benchmarks']++;
                if ($benchmark->getComputed()) {
                    $counts['computed']++;
                } else {
                    $counts['collected']++;
                }
            }
        }

        return array(
            'benchmarkGroups' => $benchmarkGroups,
            'study' => $study,
            'yearsToShow' => $years,
            //'sparklines' => $sparklines,
            'activeCollege' => $this->currentCollege(),
            'organization' => $organization,
            'counts' => $counts
        );
    }

    public function addAction()
    {
        $benchmarkGroupId = $this->params('benchmarkGroup');
        $benchmarkGroupModel = $this->getBenchmarkGroupModel();

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
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getBenchmarkModel()->save($benchmark);
                $this->getServiceLocator()->get('em')->flush();

                $extraMessage = ''; //$this->generateObservation();

                $this->flashMessenger()->addSuccessMessage('Benchmark saved. ' . $extraMessage);
                return $this->redirect()->toRoute(
                    'benchmark',
                    array('study' => $benchmarkGroup->getStudy()->getId())
                );
            }
        } else {
            // Set default years
            $form->get('yearsAvailable')
                ->setValue(array(2009,2010,2011,2012,2013,2014,2015,2016,2017,2018,2019,2020));
        }

        return array(
            'form' => $form,
            'benchmarkGroup' => $benchmarkGroup
        );
    }

    public function editAction()
    {
        $benchmarkId = $this->params('id');
        if (empty($benchmarkId) && $this->getRequest()->isPost()) {
            $benchmarkId = $this->params()->fromPost('id');
        }

        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

        // Don't proceed if the benchmark isn't found
        if (empty($benchmark)) {
            throw new \Exception('Benchmark not found.');
        }

        // Get the form
        $form = $this->getBenchmarkForm($benchmark);

        // Handle form submission
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $data = $this->params()->fromPost();
            if (empty($data['computeAfter'])) {
                $data['computeAfter'] = null;
            }

            $form->setData($data);

            if ($form->isValid()) {
                // Delete?
                $buttons = $this->params()->fromPost('buttons');

                if (!empty($buttons['delete'])) {
                    $this->getBenchmarkModel()->delete($benchmark);

                    $this->flashMessenger()->addSuccessMessage('Benchmark deleted.');

                    return $this->redirect()->toRoute('benchmark');
                }

                // Save it
                $this->getBenchmarkModel()->save($benchmark);
                $this->getServiceLocator()->get('em')->flush();

                $extraMessage = ''; //$this->generateObservation();

                $this->flashMessenger()->addSuccessMessage('Benchmark saved. ' . $extraMessage);

                // Check equation
                $computedFields = $this->getComputedFieldsService();

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
     * @return \Mrss\Service\ComputedFields
     */
    public function getComputedFieldsService()
    {
        $computedFields = $this->getServiceLocator()->get('computedFields');

        return $computedFields;
    }

    /**
     * Show a list of benchmarks that can be added to the equation
     *
     * @return ViewModel
     */
    public function equationAction()
    {
        // Get the studies
        //$studies = $this->getServiceLocator()->get('model.study')->findAll();

        $viewModel = new ViewModel(
            array(
                'studies' => array($this->currentStudy())
            )
        );
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function checkEquationAction()
    {
        $equation = $this->params()->fromPost('equation');
        $service = $this->getComputedFieldsService();
        $result = $service->checkEquation($equation);
        $error = $service->getError();

        $viewModel = new JsonModel(
            array(
                'result' => $result,
                'error' => $error
            )
        );

        return $viewModel;
    }

    public function onReportAction()
    {
        $benchmarkId = $this->params()->fromRoute('id');
        $onReport = $this->params()->fromRoute('value');

        if ($benchmarkId) {
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

            $benchmark->setIncludeInNationalReport($onReport);
            $this->getBenchmarkModel()->save($benchmark);
            $this->getBenchmarkModel()->getEntityManager()->flush();
        }


        $response = $this->getResponse()->setContent('ok');
        return $response;
    }

    public function reorderAction()
    {
        $benchmarkGroupId = $this->params()->fromPost('benchmarkGroupId');
        $newSequences = $this->params()->fromPost('benchmarks', array());
        $newHeadingSequences = $this->params()
            ->fromPost('headings', array());
        $newSequences = array_flip($newSequences);
        $newHeadingSequences = array_flip($newHeadingSequences);
        $benchmarkIds = array_keys($newSequences);

        $benchmarkGroupModel = $this->getBenchmarkGroupModel();

        $benchmarkGroup = $benchmarkGroupModel->find($benchmarkGroupId);

        $user = $this->zfcUserAuthentication()->getIdentity();
        $organization = $user->getAdminBenchmarkSorting();

        if (!empty($benchmarkGroup)) {
            $benchmarks = $this->getBenchmarkModel()->findByIds($benchmarkIds);

            foreach ($benchmarks as $benchmark) {
                if (isset($newSequences[$benchmark->getId()])) {
                    $sequence = $newSequences[$benchmark->getId()];

                    if ($organization == 'report') {
                        $benchmark->setReportSequence($sequence);
                    } else {
                        $benchmark->setSequence($sequence);
                    }

                    $benchmark->setBenchmarkGroup($benchmarkGroup);
                }
            }

            $headings = $benchmarkGroup->getBenchmarkHeadings($organization);
            foreach ($headings as $heading) {
                if (isset($newHeadingSequences[$heading->getId()])) {
                    $heading->setSequence(
                        $newHeadingSequences[$heading->getId()]
                    );
                    $heading->setBenchmarkGroup($benchmarkGroup);
                }
            }

            $this->getEntityManager()->flush();
        }

        //print_r($benchmarkGroupId); print_r($benchmarks); die;

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    public function dataAction()
    {
        $year = $this->params()->fromRoute('year');
        $benchmarkId = $this->params()->fromRoute('id');

        $benchmark = $this->getBenchmarkModel()->find($benchmarkId);
        $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear($this->currentStudy()->getId(), $year);

        return array(
            'benchmark' => $benchmark,
            'subscriptions' => $subscriptions,
            'year' => $year
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
        $entityManager = $this->getEntityManager();
        $benchmark->setEquationValidator(
            $this->getServiceLocator()->get('validator.equation')
        );

        // Pass in the entity manager for checking uniqueness
        $benchmark->setEntityManager($entityManager);

        // Build form
        $form = new Form\Benchmark;

        $form->setHydrator(new DoctrineHydrator($entityManager, 'Mrss\Entity\Benchmark'));
        $form->bind($benchmark);

        return $form;
    }

    public function setBenchmarkModel(\Mrss\Model\Benchmark $model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        if (empty($this->benchmarkModel)) {
            $this->benchmarkModel = $this->getServiceLocator()
                ->get('model.benchmark');
        }

        return $this->benchmarkModel;
    }
}
