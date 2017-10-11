<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend;

class IndexController extends BaseController
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

        // News page
        $news = null;
        if ($newsPageId = $this->getStudyConfig()->news_page_id) {
            $news = $this->getPageModel()->find($newsPageId);
        }

        $viewParams = array(
            'systems' => $systems,
            'observation' => $this->currentObservation($year),
            'yearRange' => $yearRange,
            'year' => $year,
            'observationModel' => $this->getServiceLocator()->get('model.observation'),
            'college' => $college,
            'news' => $news
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
