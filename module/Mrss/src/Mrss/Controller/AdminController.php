<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{
    public function dashboardAction()
    {
        //$this->emailTest();

        // Get a list of subscriptions to the current study
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $studyId = $this->currentStudy()->getId();

        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        $subscriptions = $subscriptionModel->findByStudyAndYear(
            $studyId,
            $year
        );


        // Years for tabs
        $years = $this->getServiceLocator()->get('model.subscription')
            ->getYearsWithSubscriptions($this->currentStudy());
        rsort($years);

        // Total
        $total = 0;
        foreach ($subscriptions as $sub) {
            $total += $sub->getPaymentAmount();
        }

        return array(
            'subscriptions' => $subscriptions,
            'years' => $years,
            'currentYear' => $year,
            'total' => $total
        );
    }

    public function changesAction()
    {
        $currentStudy = $this->currentStudy();

        $changeSetModel = $this->getServiceLocator()->get('model.changeSet');
        $changeSets = $changeSetModel->findByStudy($currentStudy->getId());

        return array(
            'changeSets' => $changeSets
        );
    }

    protected function emailTest()
    {
        $email = $this->params()->fromGet('email');

        if (!empty($email)) {
            // The template used by the PhpRenderer to create the content of the mail
            $viewTemplate = 'mrss/email/test';

            $from = 'no-reply@maximizingresources.org';

            $toEmail = $this->params()->fromQuery('to', 'personman2@gmail.com');

            $uri = $this->getRequest()->getUri();
            $subject = 'Email test from ' . $uri->getHost();

            $mailService = $this->getServiceLocator()->get('goaliomailservice_message');
            $message = $mailService->createTextMessage($from, $toEmail, $subject, $viewTemplate);
            $mailService->send($message);

            $this->flashMessenger()->addSuccessMessage('Email sent to ' . $toEmail);
        }
    }
}
