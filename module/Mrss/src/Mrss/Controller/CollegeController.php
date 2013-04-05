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

        return array(
            'colleges' => $Colleges->findAll()
        );
    }

    public function viewAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');
        $college = $Colleges->find($this->params('id'));

        // Handle invalid id
        if (empty($college)) {
            $this->flashMessenger()->addErrorMessage("Invalide college id.");
            return $this->redirect()->toUrl('/colleges');
        }

        return array('college' => $college);
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
