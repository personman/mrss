<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $year = $this->currentStudy()->getCurrentYear();

        // Get this year's memberships by network
        $subscriptions = $this->getCollege()->getSystemsByYear($year);

        $viewParams = array();

        return new ViewModel($viewParams);
    }

    public function glossaryAction()
    {
        return new ViewModel();
    }

    /**
     * @return \Mrss\Entity\College
     */
    protected function getCollege()
    {
        if ($user = $this->zfcUserAuthentication()->getIdentity()) {
            return $user->getCollege();
        }
    }
}
