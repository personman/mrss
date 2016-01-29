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

    public function downloadUsersAction()
    {
        $colleges = array();
        $allowedRoles = array('data', 'system_admin', 'staff');

        $issues = $this->getIssueModel()->findByStatus(array(), array('adminConfirmed', 'userConfirmed'));

        foreach ($issues as $issue) {
            $college = $issue->getCollege();
            if (empty($colleges[$college->getId()])) {
                $users = $college->getUsersByStudy($this->currentStudy());

                $userData = array();

                foreach ($users as $user) {
                    if (in_array($user->getRole(), $allowedRoles)) {
                        $userData[] = array(
                            $user->getEmail(),
                            $user->getPrefix(),
                            $user->getFirstName(),
                            $user->getLastName(),
                            $user->getTitle(),
                            $college->getName()
                        );
                    }
                }

                $colleges[$college->getId()] = $userData;
            }
        }

        // Flatten the array
        $newArray = array(array('E-mail', 'Prefix', 'First Name', 'Last Name', 'Title', 'Institution'));
        foreach ($colleges as $userData) {
            $newArray = array_merge($newArray, $userData);
        }

        $excel = new \PHPExcel();
        $sheet = $excel->getActiveSheet();

        $sheet->fromArray($newArray, null, 'A1');

        foreach (range(0, 5) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $filename = 'users-with-data-issues.xlsx';

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
    }

    public function downloadExcel($excel, $filename)
    {
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }


    /**
     * @return \Mrss\Model\Issue
     */
    protected function getIssueModel()
    {
        return $this->getServiceLocator()->get('model.issue');
    }
}
