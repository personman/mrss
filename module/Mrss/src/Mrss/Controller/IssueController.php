<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IssueController extends AbstractActionController
{
    public function indexAction()
    {
        $issues = $this->getIssueModel()->findByCollege($this->currentCollege());

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
