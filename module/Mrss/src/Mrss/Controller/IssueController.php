<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\IssueUserNote;
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
            if ($issue) {
                $issue->setStatus($status);

                $this->getIssueModel()->save($issue);
                $this->getIssueModel()->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage('Issue updated.');

            }
        }

        return $this->redirect()->toRoute('issues/staff');
    }

    public function massUpdateAction()
    {
        if ($this->getRequest()->isPost()) {

            $buttons = $this->params()->fromPost('buttons');

            if (!empty($buttons['sendBack'])) {
                $status = null;
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
        $issues = $this->getIssueModel()->findByStatus(array(), array('adminConfirmed'));

        return array(
            'issues' => $issues
        );
    }

    /**
     * @return \Mrss\Model\Issue
     */
    protected function getIssueModel()
    {
        return $this->getServiceLocator()->get('model.issue');
    }
}
