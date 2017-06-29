<?php

namespace Mrss\Service;

use Mrss\Entity\Issue;
use Mrss\Model\Issue as IssueModel;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Entity\College;

class Validation
{
    protected $study;

    protected $user;

    protected $issueModel;

    protected $benchmarkModel;

    protected $validator;

    protected $changeSet;

    protected $existingIssues = array();

    protected $issues = array();

    public function validate($observation, $priorObservation = null)
    {
        $this->collectExistingIssues($observation->getCollege());

        $validator = $this->getValidator();

        if (!empty($validator)) {
            $validator->setBenchmarkModel($this->getBenchmarkModel());

            $issues = $validator->runValidation($observation, $priorObservation);
            $this->saveIssues($issues, $observation);
        }

        $this->clearResolvedIssues();

        $issues = $this->getIssues();

        return $issues;
    }

    protected function collectExistingIssues(College $college)
    {
        // We want to key these with the error code
        $issues = $this->getIssueModel()->findByCollege($college);
        $keyedIssues = array();

        foreach ($issues as $issue) {
            $keyedIssues[$issue->getErrorCode()] = $issue;
        }

        $this->existingIssues = $keyedIssues;
    }

    public function saveIssues($issues, $observation)
    {
        foreach ($issues as $issueInfo) {
            $errorCode = $issueInfo['errorCode'];

            // Do we already have a row for this issue?
            if (!empty($this->existingIssues[$errorCode])) {
                $issue = $this->existingIssues[$errorCode];
                unset($this->existingIssues[$errorCode]);
            } else {
                $issue = new Issue;

                $issue->setStudy($this->getStudy());
                $issue->setYear($observation->getYear());
                $issue->setCollege($observation->getCollege());
                $issue->setErrorCode($errorCode);
                $issue->setFormUrl($issueInfo['formUrl']);
            }

            $issue->setMessage($issueInfo['message']);
            $issue->setUser($this->getUser());

            if ($changeSet = $this->getChangeSet()) {
                $issue->setChangeSet($changeSet);
            }

            // Hold on to non-resolved issues to alert the user of them
            if (!$issue->getStatus()) {
                $this->issues[] = $issue;
            }

            $this->getIssueModel()->save($issue);
        }

        $this->getIssueModel()->getEntityManager()->flush();
    }

    protected function clearResolvedIssues()
    {
        // Any issues left in this list now can be dropped
        foreach ($this->existingIssues as $issue) {
            $this->getIssueModel()->delete($issue);
        }

        $this->getIssueModel()->getEntityManager()->flush();
    }

    protected function getIssues()
    {
        return $this->issues;
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

    public function setBenchmarkModel(BenchmarkModel $benchmarkModel)
    {
        $this->benchmarkModel = $benchmarkModel;

        return $this;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
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
