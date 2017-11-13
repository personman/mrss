<?php

namespace Mrss\Controller;

use Mrss\Service\Export\User as ExportUser;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\IssueUserNote;
use Mrss\Entity\Suppression;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class IssueController extends AbstractActionController
{
    public function indexAction()
    {
        $issues = $this->getIssueModel()->findForCollege($this->currentCollege());
        $em = $this->getIssueModel()->getEntityManager();

        // Build a form for each issue
        $forms = array();
        $filteredIssues = array();
        foreach ($issues as $issue) {
            $form = new IssueUserNote();


            $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\Issue'));
            $form->bind($issue);

            $forms[$issue->getId()] = $form;
            $filteredIssues[] = $issue;
        }

        return array(
            'issues' => $filteredIssues,
            'forms' => $forms
        );
    }

    /**
     * Accept submission of form IssueUserNote
     */
    public function noteAction()
    {
        // First, find the issue
        $id = $this->params()->fromPost('id');

        if (!empty($id)) {
            // Now look it up
            $issue = $this->getIssueModel()->find($id);

            // Make sure the issue was found and belongs to this user's college
            if (!empty($issue) && $issue->getCollege()->getId() == $this->currentCollege()->getId()) {
                // Set the status
                $issue->setStatus('userConfirmed');

                // Set the note
                $note = $this->params()->fromPost('userNote');
                $issue->setUserNote($note);

                // Save it
                $this->getIssueModel()->save($issue);
                $this->getIssueModel()->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage("Saved.");
            } else {
                $this->flashMessenger()->addErrorMessage('There was a problem updating your issue.');
            }
        }

        return $this->redirect()->toRoute('issues');
    }

    public function updateAction()
    {
        if ($this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
            $status = $this->params()->fromPost('status');
            if ($status == 'null') {
                $status = null;
            }

            $issue = $this->getIssueModel()->find($id);

            // Suppress form?
            if ($status == 'suppressed') {
                $this->suppressForm($issue);
            }

            // Save the issue
            if ($issue) {
                $issue->setStatus($status);

                $this->getIssueModel()->save($issue);
                $this->getIssueModel()->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage('Issue updated.');
            }
        }

        return $this->redirect()->toRoute('issues/staff');
    }

    /**
     * @param \Mrss\Entity\Issue $issue
     */
    public function suppressForm($issue)
    {
        $formUrl = $issue->getFormUrl();
        $benchmarkGroup = $this->getBenchmarkGroupModel()->findOneByUrlAndStudy($formUrl, $this->currentStudy());
        $subscription = $this->getSubscriptionModel()->findOne(
            $issue->getYear(),
            $issue->getCollege()->getId(),
            $this->currentStudy()->getId()
        );

        // Does it exist already?
        $existing = $this->getSuppressionModel()->findBySubscriptionAndBenchmarkGroup($subscription, $benchmarkGroup);
        if (!$existing) {
            $suppression = new Suppression();
            $suppression->setSubscription($subscription);
            $suppression->setBenchmarkGroup($benchmarkGroup);

            $this->getSuppressionModel()->save($suppression);

            // Flush happens later
        }
    }

    public function massUpdateAction()
    {
        if ($this->getRequest()->isPost()) {
            $buttons = $this->params()->fromPost('buttons');

            if (!empty($buttons['sendBack'])) {
                $status = null;
            } elseif (!empty($buttons['suppress'])) {
                $status = 'suppressed';
            } elseif (!empty($buttons['confirm'])) {
                $status = 'adminConfirmed';
            } else {
                $this->flashMessenger()->addErrorMessage('Problem updating issues. Be sure to click a button.');
                return $this->redirect()->toRoute('issues/staff');
            }

            $issueCheckboxes = $this->params()->fromPost('issue', array());
            $issueIds = array_keys($issueCheckboxes);

            $count = 0;
            foreach ($issueIds as $issueId) {
                $issue = $this->getIssueModel()->find($issueId);
                if ($issue) {
                    $issue->setStatus($status);

                    if ($status == 'suppressed') {
                        $this->suppressForm($issue);
                    }

                    $this->getIssueModel()->save($issue);
                    $count++;
                }
            }

            $this->getIssueModel()->getEntityManager()->flush();

            $noun = 'Issues';
            if ($count == 1) {
                $noun = 'Issue';
            }

            $this->flashMessenger()->addSuccessMessage("$count $noun updated.");
        }

        return $this->redirect()->toRoute('issues/staff');
    }

    public function staffAction()
    {
        $issues = $this->getIssueModel()->findByStatus(array(), array('adminConfirmed', 'suppressed'));

        return array(
            'issues' => $issues
        );
    }

    public function downloadUsersAction()
    {
        $allowedRoles = array('data', 'system_admin', 'staff');

        $issues = $this->getIssueModel()
            ->findByStatus(array(), array('adminConfirmed', 'userConfirmed', 'suppressed'));

        $users = array();
        $collegesProcessed = array();

        foreach ($issues as $issue) {
            $college = $issue->getCollege();

            // Has this college been handled already?
            if (!empty($collegesProcessed[$college->getId()])) {
                continue;
            }

            $collegeUsers = $college->getUsersByStudy($this->currentStudy());


            foreach ($collegeUsers as $user) {
                if (in_array($user->getRole(), $allowedRoles)) {
                    $users[] = $user;
                }
            }

            $collegesProcessed[$college->getId()] = true;
        }


        $exporter = new ExportUser();
        $exporter->export($users);
    }

    /**
     * @return \Mrss\Model\Issue
     */
    protected function getIssueModel()
    {
        return $this->getServiceLocator()->get('model.issue');
    }

    /**
     * @return \Mrss\Model\BenchmarkGroup
     */
    protected function getBenchmarkGroupModel()
    {
        return $this->getServiceLocator()->get('model.benchmark.group');
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        return $this->getServiceLocator()->get('model.subscription');
    }

    /**
     * @return \Mrss\Model\Suppression
     */
    protected function getSuppressionModel()
    {
        return $this->getServiceLocator()->get('model.suppression');
    }
}
