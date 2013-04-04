<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class CollegeController extends AbstractActionController
{

    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $em = $sm->get('doctrine.entitymanager.orm_default');

        $Colleges = new \Mrss\Model\College();
        $Colleges->setEntityManager($em);

        return array('colleges' => $Colleges->findAll());
    }
}
