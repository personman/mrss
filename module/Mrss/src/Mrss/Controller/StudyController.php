<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Study;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;

class StudyController extends AbstractActionController
{
    public function indexAction()
    {
        $studyModel = $this->getServiceLocator()->get('model.study');

        return array(
            'studies' => $studyModel->findAll()
        );
    }

    public function viewAction()
    {
        
    }

    public function completionAction()
    {
        set_time_limit(1200);
        ini_set('memory_limit', '256M');

        // Turn off query logging
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);

        $id = $this->params('id');
        $studyModel = $this->getServiceLocator()->get('model.study');
        $study = $studyModel->find($id);

        if (empty($study)) {
            throw new \Exception('Study not found.');
        }

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

        $studyModel = $this->getServiceLocator()->get('model.study');
        $study = $studyModel->find($id);

        if (empty($study)) {
            throw new \Exception('Study not found.');
        }

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
                $studyModel->save($study);

                $this->flashMessenger()->addSuccessMessage('Study saved.');
                return $this->redirect()->toRoute('studies');
            }

        }

        return array(
            'form' => $form
        );
    }
}
