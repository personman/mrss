<?php

namespace Mrss\Controller;

use Mrss\Service\Report\Chart\Line;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Study;
use Mrss\Model\Study as StudyModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class StudyController extends AbstractActionController
{
    /**
     * @var \Mrss\Model\Study
     */
    protected $studyModel;

    public function indexAction()
    {
        return array(
            'studies' => $this->getStudyModel()->findAll()
        );
    }

    public function viewAction()
    {
        $studyId = $this->params('id');
        $study = $this->getStudy($studyId);

        return array(
            'study' => $study
        );
    }

    public function completionAction()
    {
        takeYourTime();

        // Turn off query logging so we don't exhaust our RAM
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);

        $id = $this->params('id');
        $study = $this->getStudyModel()->find($id);

        $collegeModel = $this->getServiceLocator()->get('model.college');
        $colleges = $collegeModel->findAll();

        // Years
        $years = range(2014, date('Y'));

        return array(
            'study' => $study,
            'years' => $years,
            'colleges' => $colleges
        );
    }

    public function infoAction()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $years = array();
        foreach ($this->getSubscriptionModel()->getYearsAndRevenue($study) as $year => $info) {
            // Total members
            /*$subscriptionCount = $this->getSubscriptionModel()->countByStudyAndYear(
                $study->getId(),
                $year
            );*/

            $count = $info['count'];
            $revenue = $info['revenue'];


            $years[$year] = array(
                'count' => $count,
                'revenue' => $revenue
            );
        }



        $year = $study->getCurrentYear();


        $params = array(
            'year' => $year,
            'study' => $study->getName(),
            'studyDescription' => $study->getDescription(),
            'memberships' => $years,
            'progress' => $this->getSubscriptionProgressChart()
        );

        $viewModel = new JsonModel($params);

        return $viewModel;
    }

    protected function getSubscriptionProgressChart()
    {


        $chart = new Line();

        $series = array();
        foreach ($this->getProgressChartData() as $year => $data) {
            $series[] = array(
                'name' => $year,
                'data' => array_values($data)
            );
        }



        $chart->setTitle('NCCBP Membership');
        $chart->setSeries($series)
            ->setXLabel('Days')
            ->setYLabel('Members')
            ->setWidth('half');


        $config = $chart->getConfig();
        unset($config['chart']['events']);

        return $config;
    }

    protected function getProgressChartData()
    {
        $allData = array();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        foreach ($this->getSubscriptionModel()->getYearsWithSubscriptions($study) as $year) {
            if ($year < 2013) {
                continue;
            }

            $subs = $this->getSubscriptionModel()->findByStudyAndYear($study->getId(), $year, false, 's.created ASC');

            $firstSubDate = null;
            $count = 0;
            $subsInfo = array();
            foreach ($subs as $subscription) {
                $count++;

                if ($this->isDateInvalid($subscription->getCreated())) {
                    continue;
                }

                if (empty($firstSubDate)) {
                    $firstSubDate = $subscription->getCreated();
                }

                $daysAfterFirst = $subscription->getCreated()->diff($firstSubDate)->days;
                //pr($daysAfterFirst);

                $subsInfo[$daysAfterFirst] = $count;
            }

            $subsInfo = $this->fillInGaps($subsInfo);

            $allData[$year] = $subsInfo;
        }

        return $allData;
    }

    protected function fillInGaps($subsInfo)
    {
        $max = max(array_keys($subsInfo));
        $range = range(0, $max);

        $total = 0;
        $newSubsInfo = array();
        foreach ($range as $key) {
            if (isset($subsInfo[$key])) {
                $total = $subsInfo[$key];
            }

            $newSubsInfo[$key] = $total;
        }

        return $newSubsInfo;
    }

    protected function isDateInvalid(\DateTime $date)
    {
        return ($date->format('Y') == '-0001');
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        return $this->getServiceLocator()->get('model.subscription');
    }

    public function editAction()
    {
        //$c = $this->currentStudy();
        //var_dump($c); die;

        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $study = $this->getStudy($id);

        $form = new Study;
        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Study'
            )
        );
        $form->bind($study);

        // Handle form submission
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                //var_dump($this->params()->fromPost());
                //var_dump($study); die;
                $this->getStudyModel()->save($study);

                $this->flashMessenger()->addSuccessMessage('Study saved.');
                return $this->redirect()->toRoute('studies');
            }
        }

        return array(
            'form' => $form
        );
    }

    /**
     * @deprecated
     * @return array
     * @throws \Exception
     */
    public function importAction()
    {
        //return array();

        takeYourTime();

        $studyId = $this->params()->fromRoute('id');
        $filename = $this->getCsvImportFileForStudy($studyId);

        /** @var \Mrss\Service\ImportBenchmarks $importer */
        $importer = $this->getServiceLocator()->get('import.csv');

        // Pass in the study we're importing to
        $studyModel = $this->getServiceLocator()->get('model.study');
        $study = $studyModel->find($studyId);
        $importer->setStudy($study);

        $importer->import($filename);
        $this->getServiceLocator()->get('em')->flush();

        // Output properties that need to be added to Observation
        return array(
            'messages' => $importer->getMessages()
        );
    }

    public function exportAction()
    {
        $studyId = $this->params()->fromRoute('id');
        $study = $this->getStudy($studyId);

        $filename = $this->getCsvImportFileForStudy($studyId);

        /** @var \Mrss\Service\ImportBenchmarks $importer */
        $importer = $this->getServiceLocator()->get('import.csv');

        $importer->export($study, $filename);

        $this->flashMessenger()->addSuccessMessage('Study exported to ' . $filename);
        return $this->redirect()->toRoute(
            'benchmark',
            array('study' => $study->getId())
        );
    }

    public function getCsvImportFileForStudy($studyId)
    {
        $csvFiles = array(
            //1 => 'data/imports/nccbp-benchmarks.csv',
            1 => 'data/imports/nccwtp-benchmarks.csv',
            2 => 'data/imports/mrss-benchmarks.csv',
            3 => 'data/imports/nccwtp-benchmarks.csv',
            4 => 'data/imports/aaup-benchmarks.csv'
        );

        if (empty($csvFiles[$studyId])) {
            throw new \Exception('Import file not found for study ' . $studyId);
        }

        return $csvFiles[$studyId];
    }

    /**
     * List all the benchmarks with their data definitions
     */
    public function dictionaryAction()
    {
        $study = $this->currentStudy();

        return array(
            'study' => $study,
            'variable' => $this->getServiceLocator()->get('service.variableSubstitution')
        );
    }

    public function calculationsAction()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        /** @var \Mrss\Service\ComputedFields $computedFields */
        $computedFields = $this->getServiceLocator()->get('computedFields');

        $variableService = $this->getServiceLocator()->get('service.variableSubstitution');
        $computedFields->setVariableService($variableService);

        $year = $study->getCurrentYear();

        $keyedBenchmarks = $this->getBenchmarksFromStudy($study);
        $computedBenchmarks = array();
        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            $computed = array();

            $benchmarks = $benchmarkGroup->getChildren($year, true, 'report', 'computed');
            foreach ($benchmarks as $benchmark) {
                if (get_class($benchmark) == 'Mrss\Entity\BenchmarkHeading') {
                    /** @var \Mrss\Entity\BenchmarkHeading $heading */
                    $heading = $benchmark;
                    $computed[] = array(
                        'heading' => true,
                        'name' => $variableService->substitute($heading->getName()),
                        'description' => $variableService->substitute($heading->getDescription())
                    );
                    continue;
                }

                $equation = $computedFields->getEquationWithLabels($benchmark, false);

                $computed[] = array(
                    'benchmark' => $variableService->substitute($benchmark->getReportLabel()),
                    'equation' => $equation
                );
            }

            $computedBenchmarks[] = array(
                'name' => $benchmarkGroup->getName(),
                'number' => $benchmarkGroup->getUrl(),
                'benchmarks' => $computed
            );
        }

        return array(
            'computedBenchmarks' => $computedBenchmarks
        );
    }

    /**
     * @param \Mrss\Entity\Study $study
     * @return array
     */
    protected function getBenchmarksFromStudy($study)
    {
        $year = $study->getCurrentYear();
        $benchmarks = array();
        foreach ($study->getBenchmarksForYear($year) as $benchmark) {
            $benchmarks[$benchmark->getDbColumn()] = $benchmark;
        }

        return $benchmarks;
    }

    /**
     * @param integer $id
     * @throws \Exception
     * @return Study
     */
    protected function getStudy($id)
    {
        $study = $this->getStudyModel()->find($id);

        if (empty($study)) {
            throw new \Exception('Study not found.');
        }

        return $study;
    }

    /**
     * @param StudyModel $studyModel
     * @return $this
     */
    public function setStudyModel(StudyModel $studyModel)
    {
        $this->studyModel = $studyModel;

        return $this;
    }

    /**
     * @return StudyModel
     */
    protected function getStudyModel()
    {
        if (empty($this->studyModel)) {
            $this->studyModel = $this->getServiceLocator()->get('model.study');
        }

        return $this->studyModel;
    }
}
