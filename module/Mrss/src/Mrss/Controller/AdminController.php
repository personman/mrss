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
