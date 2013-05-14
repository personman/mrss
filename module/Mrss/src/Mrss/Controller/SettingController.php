<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class SettingController extends AbstractActionController
{

    public function indexAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array(
            'colleges' => $Colleges->findAll()
        );
    }
}
