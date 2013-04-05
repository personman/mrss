<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

class ObservationController extends AbstractActionController
{
    public function viewAction()
    {
        $Observations = $this->getServiceLocator()->get('model.observation');

        return array(
            'observation' => $Observations->find($this->params('id')),
            'fields' => $this->getFields()
        );
    }

    protected function getFields()
    {
        return array(
            'tot_fte_career_staff' => 'Career Staff'
        );
    }
}
