<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    public function dashboardAction()
    {
        // Get a list of subscriptions to the current study
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();
        $currentYear = $this->currentStudy()->getCurrentYear();

        $subscriptions = $subscriptionModel->findByStudyAndYear(
            $studyId,
            $currentYear
        );

        return array(
            'subscriptions' => $subscriptions
        );
    }
}
