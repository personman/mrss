<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    public function dashboardAction()
    {
        $this->emailTest();

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

    protected function emailTest()
    {
        if (!empty($_GET['email'])) {
            // The template used by the PhpRenderer to create the content of the mail
            $viewTemplate = 'mrss/email/test';

            $from = 'no-reply@maximizingresources.org';
            $to = 'personman2@gmail.com';

            if (!empty($_GET['to'])) {
                $to = $_GET['to'];
            }

            $subject = 'Email test from ' . $_SERVER['HTTP_HOST'];

            $mailService = $this->getServiceLocator()->get('goaliomailservice_message');
            $message = $mailService->createTextMessage($from, $to, $subject, $viewTemplate);
            $mailService->send($message);

            $this->flashMessenger()->addSuccessMessage('Email sent to ' . $to);
        }
    }
}
