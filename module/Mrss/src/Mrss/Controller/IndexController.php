<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $college = $this->getCollege();

        if (empty($college)) {
            return $this->redirect()->toUrl('/user/login');
        }


        $this->layout()->noWrapper = true;
        $this->layout()->wrapperId = 'home';

        $year = $this->currentStudy()->getCurrentYear();

        // Get this year's memberships by network
        $systems = $college->getSystemsByYear($year);
        //$nextYear = $year + 1;
        //$yearRange = "$year - $nextYear";
        $yearRange = "FY $year";

        $viewParams = array(
            'systems' => $systems,
            'observation' => $this->currentObservation(),
            'yearRange' => $yearRange,
            'year' => $year
        );

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
