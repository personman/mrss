<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
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
}
