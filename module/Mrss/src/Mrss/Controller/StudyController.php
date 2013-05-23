<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Study;
use Mrss\Model\Study as StudyModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;

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
        $id = $this->params('id');
        $study = $this->getStudy($id);

        return array(
            'study' => $study
        );
    }

    public function completionAction()
    {
        set_time_limit(1200);
        ini_set('memory_limit', '256M');

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
        $years = range(2007, date('Y'));

        return array(
            'study' => $study,
            'years' => $years,
            'colleges' => $colleges
        );
    }

    public function editAction()
    {
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
