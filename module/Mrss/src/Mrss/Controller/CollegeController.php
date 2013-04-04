<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class CollegeController extends AbstractActionController
{

    public function indexAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array('colleges' => $Colleges->findAll());
    }

    /**
     * This is very slow. Need to store the lat/lng of each college instead of
     * letting the map script look it up by address.
     *
     * @return array
     */
    public function mapAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array('colleges' => $Colleges->findAll());
    }
}
