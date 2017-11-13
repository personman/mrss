<?php

namespace Mrss\Controller;

use Mrss\Entity\BenchmarkGroup;
use Mrss\Model\BenchmarkGroup as BenchmarkGroupModel;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class BenchmarkGroupController extends AbstractActionController
{

    /**
     * @var BenchmarkGroupModel
     */
    protected $benchmarkGroupModel;

    public function editAction()
    {
        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $benchmarkGroup = $this->getBenchmarkGroupModel()->find($id);

        // Don't proceed if the benchmark isn't found
        if (empty($benchmarkGroup)) {
            throw new \Exception('Benchmark not found.');
        }

        // Get the form
        $form = $this->getBenchmarkGroupForm($benchmarkGroup);

        // Handle form submission
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getBenchmarkGroupModel()->save($benchmarkGroup);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()
                    ->addSuccessMessage('Benchmark group saved.');

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

    public function addAction()
    {
        $studyId = $this->params('study');

        $study = $this->getServiceLocator()->get('model.study')
            ->find($studyId);

        if (empty($study)) {
            throw new \Exception('Study not found.');
        }

        $benchmarkGroup = new BenchmarkGroup();
        $benchmarkGroup->setStudy($study);

        $form = $this->getBenchmarkGroupForm($benchmarkGroup);

        // Handle form submission
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getBenchmarkGroupModel()->save($benchmarkGroup);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()
                    ->addSuccessMessage('Benchmark group saved.');

                return $this->redirect()->toRoute(
                    'benchmark',
                    array('study' => $study->getId())
                );
            }
        }

        return array(
            'form' => $form,
            'study' => $study
        );
    }

    public function setBenchmarkGroupModel(BenchmarkGroupModel $model)
    {
        $this->benchmarkGroupModel = $model;

        return $this;
    }

    public function getBenchmarkGroupModel()
    {
        if (empty($this->benchmarkGroupModel)) {
            $this->benchmarkGroupModel = $this->getServiceLocator()
                ->get('model.benchmark.group');
        }

        return $this->benchmarkGroupModel;
    }


    /**
     * Get the form and bind the entity
     *
     * @param \Mrss\Entity\BenchmarkGroup $benchmarkGroup
     * @return \Zend\Form\Form
     */
    public function getBenchmarkGroupForm(BenchmarkGroup $benchmarkGroup)
    {
        $em = $this->getServiceLocator()->get('em');

        // Build form
        $form = new \Mrss\Form\BenchmarkGroup;

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\BenchmarkGroup'));
        $form->bind($benchmarkGroup);

        return $form;
    }
}
