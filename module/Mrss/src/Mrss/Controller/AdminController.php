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
            12
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
        takeYourTime();

        $collegeId = $this->params()->fromRoute('college');

        if ($collegeId) {
            $changeSets = $this->getChangeSetModel()->findByStudyAndCollege(
                $this->getStudy()->getId(),
                $collegeId
            );
        } else {
            $changeSets = $this->getChangeSetModel()->findByStudy($this->getStudy()->getId());
        }

        return array(
            'changeSets' => $changeSets
        );
    }

    public function equationsAction()
    {
        $results = array();

        foreach ($this->currentStudy()->getAllBenchmarks() as $benchmark) {
            /** @var \Mrss\Entity\Benchmark $benchmark */
            if ($benchmark->getComputed()) {
                $equation = $benchmark->getEquation();
                $editLink = " <a href='/benchmark/study/0/edit/{$benchmark->getId()}'>Edit</a>";

                if (empty($equation)) {
                    $results[] = $benchmark->getDbColumn() . " is computed but has no equation. " . $editLink;
                } elseif (!$this->getComputedFieldsService()->checkEquation($equation)) {
                    $base = "Equation error for " . $benchmark->getDbColumn() . ". ";

                    $results[] = $base . $this->getComputedFieldsService()->getError() . $editLink;
                }
            }
        }

        return array(
            'errors' => $results
        );
    }

    /**
     * @return \Mrss\Service\ComputedFields
     */
    public function getComputedFieldsService()
    {
        return $this->getServiceLocator()->get('computedFields');
    }

    /**
     * Build a new Observation entity based on benchmark config
     */
    public function generateAction()
    {
        /** @var \Mrss\Service\ObservationGenerator $generator */
        $generator = $this->getServiceLocator()->get('service.generator');

        $generator->generate();

        $stats = $generator->getStats();

        prd($stats);
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
        return $this->getServiceLocator()->get('model.change.set');
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
