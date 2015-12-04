<?php

namespace Mrss\Service;

use Mrss\Entity\Issue;
use Mrss\Model\Issue as IssueModel;

class Validation
{
    protected $study;

    protected $user;

    protected $issueModel;

    protected $validator;

    protected $changeSet;

    public function validate($observation, $priorObservation = null)
    {
        $this->clearExistingIssues();

        $issues = array();
        $validator = $this->getValidator();

        if (!empty($validator)) {
            $issues = $validator->runValidation($observation, $priorObservation);
            $this->saveIssues($issues, $observation);
        }

        return $issues;
    }

    protected function clearExistingIssues()
    {
        $this->getIssueModel()->deleteByCollege($this->getUser()->getCollege());
    }

    public function saveIssues($issues, $observation)
    {
        foreach ($issues as $issueInfo) {
            $issue = new Issue;
            $issue->setMessage($issueInfo['message']);
            $issue->setStudy($this->getStudy());
            $issue->setYear($observation->getYear());
            $issue->setCollege($observation->getCollege());
            $issue->setErrorCode($issueInfo['errorCode']);
            $issue->setFormUrl($issueInfo['formUrl']);
            $issue->setUser($this->getUser());

            if ($changeSet = $this->getChangeSet()) {
                $issue->setChangeSet($changeSet);
            }


            // @todo: handle existing

            $this->getIssueModel()->save($issue);
        }

        $this->getIssueModel()->getEntityManager()->flush();
    }

    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setIssueModel(IssueModel $issueModel)
    {
        $this->issueModel = $issueModel;

        return $this;
    }

    /**
     * @return \Mrss\Model\Issue
     */
    public function getIssueModel()
    {
        return $this->issueModel;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function setChangeSet($changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * @return \Mrss\Entity\ChangeSet
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Mrss\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
