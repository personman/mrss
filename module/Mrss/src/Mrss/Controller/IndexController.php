<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout()->noWrapper = true;

        return new ViewModel();
    }
}
