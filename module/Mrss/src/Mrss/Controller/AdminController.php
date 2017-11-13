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
            8
        );

        // Users queue
        $users = $this->getUserModel()->findByState(0);

        $recentlyActiveUsers = $this->getUserModel()->findByLastAccess();

        return array(
            'subscriptions' => $subscriptions,
            'subscriptionCount' => $subscriptionCount,
            'changeSets' => $changeSets,
            'userQueue' => $users,
            'recentlyActiveUsers' => $recentlyActiveUsers
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
            'total' => $total,
            'sectionsNote' => $this->getSectionsNote($subscriptions)
        );
    }

    /**
     * @param \Mrss\Entity\Subscription[] $subscriptions
     * @return string
     */
    protected function getSectionsNote($subscriptions)
    {
        $sectionCounts = array();
        foreach ($this->getStudy()->getSections() as $section) {
            $sectionCounts[$section->getId()] = array(
                'name' => $section->getName(),
                'count' => 0
            );
        }

        foreach ($subscriptions as $sub) {
            foreach ($sub->getSectionIds() as $sectionId) {
                $sectionCounts[$sectionId]['count']++;
            }
        }

        $sectionNotes = array();
        foreach ($sectionCounts as $sectionCount) {
            $sectionNotes[] = $sectionCount['name'] . ': ' . $sectionCount['count'];
        }

        $sectionNote = null;
        if (count($sectionNotes)) {
            $sectionNote = implode(', ', $sectionNotes);
        }

        if ($sectionNote) {
            $sectionNote = " ($sectionNote)";
        }

        return $sectionNote;
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
     * Just display a list of links
     */
    public function settingsAction()
    {
        return array();
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


        $strip = $this->params()->fromRoute('strip');

        if (!$strip) {
            $generator->generate(true, true);
        } else {
            $generator->stripObservation();
        }

        $stats = $generator->getStats();

        prd($stats);
    }

    public function checkMigrationAction()
    {
        takeYourTime();

        $minId = $this->params()->fromRoute('minId');

        /** @var \Mrss\Service\ObservationDataMigration $migrator */
        $migrator = $this->getServiceLocator()->get('service.observation.data.migration');

        $migrator->check($minId);
    }

    protected function getYear()
    {
        $year = $this->params()->fromRoute('year');
        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        return $year;
    }

    public function testFilterAction()
    {
        $criteria = array(
            'institution_control' => array('Public'),
            'ft_average_instructor_salary' => '6000 - 50000',
            //'ft_male_faculty_number_9_month' => '1 - 10000',
            //'ft_female_faculty_number_9_month' => '1 - 10000',
            'states' => array('MO')
        );

        $year = 2016;
        $study = $this->currentStudy();

        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');


        $start = microtime(1);
        $colleges = $collegeModel->findByCriteria(
            $criteria,
            $study,
            $this->currentCollege(),
            $year
        );

        $elapsed = round(microtime(1) - $start, 3);
        pr($elapsed);

        echo 'Count: ';
        pr(count($colleges));

        foreach ($colleges as $college) {
            $sub = $college->getSubscriptionByStudyAndYear($study->getId(), $year);

            pr($college->getNameAndState());

            foreach (array_keys($criteria) as $dbColumn) {
                $val = $sub->getValue($dbColumn);


                echo "$dbColumn = $val<br>";
            }
        }

        die(' done');
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

    public function cleanUpAction()
    {
        $study = $this->currentStudy();
    }
}
