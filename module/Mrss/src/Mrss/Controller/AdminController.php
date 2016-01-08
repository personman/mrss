<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AdminController extends AbstractActionController
{

    public function dashboardAction()
    {
        // Total members
        $subscriptionCount = $this->getSubscriptionModel()->countByStudyAndYear(
            $this->getStudy()->getId(),
            $this->getYear()
        );

        // Recent members
        $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear(
            $this->getStudy()->getId(),
            $this->getYear(),
            false,
            's.created DESC',
            10
        );

        // Recent changes
        $changeSets = $this->getChangeSetModel()->findByStudy(
            $this->getStudy()->getId(),
            7
        );

        // Users queue
        $users = $this->getUserModel()->findByState(0);

        return array(
            'subscriptions' => $subscriptions,
            'subscriptionCount' => $subscriptionCount,
            'changeSets' => $changeSets,
            'userQueue' => $users
        );
    }

    public function membershipsAction()
    {

        //$this->emailTest();

        // Get a list of subscriptions to the current study
        $subscriptionModel = $this->getSubscriptionModel();
        $studyId = $this->getStudy()->getId();

        $year = $this->getYear();
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
        $changeSets = $this->getChangeSetModel()->findByStudy($this->getStudy()->getId());

        return array(
            'changeSets' => $changeSets
        );
    }

    protected function getYear()
    {
        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        return $year;
    }

    /**
     * @return \Mrss\Model\User
     */
    protected function getUserModel()
    {
        return $this->getServiceLocator()->get('model.user');
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        return $this->getServiceLocator()->get('model.subscription');
    }

    /**
     * @return \Mrss\Model\ChangeSet
     */
    protected function getChangeSetModel()
    {
        return $this->getServiceLocator()->get('model.changeSet');
    }

    /**
     * @return \Mrss\Entity\Study
     */
    protected function getStudy()
    {
        return $this->currentStudy();
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
