<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity to track data issues
 *
 * @ORM\Entity
 * @ORM\Table(name="issues")
 */
class Issue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="benchmarkGroups")
     */
    protected $study;

    /**
     * @ORM\Column(type="integer")
     */
    protected $year;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="observations")
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="ChangeSet")
     * @ORM\JoinColumn(name="changeSet_id", referencedColumnName="id", nullable=true)
     * @var ChangeSet
     */
    protected $changeSet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $errorCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $formUrl;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="changes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $userNote;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $staffNote;

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setChangeSet(ChangeSet $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue($nullAsString = false)
    {
        $value = $this->value;

        if ($nullAsString && $value === null) {
            $value = 'null';
        }

        return $value;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function setFormUrl($url)
    {
        $this->formUrl = $url;

        return $this;
    }

    public function getFormUrl()
    {
        return $this->formUrl;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    /**
     * @return \Mrss\Entity\College
     */
    public function getCollege()
    {
        return $this->college;
    }

    public function setErrorCode($code)
    {
        $this->errorCode = $code;

        return $this;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getUserNote()
    {
        return $this->userNote;
    }

    /**
     * @param mixed $userNote
     * @return Issue
     */
    public function setUserNote($userNote)
    {
        $this->userNote = $userNote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStaffNote()
    {
        return $this->staffNote;
    }

    /**
     * @param mixed $staffNote
     * @return Issue
     */
    public function setStaffNote($staffNote)
    {
        $this->staffNote = $staffNote;

        return $this;
    }

    public function getObservation()
    {
        $observation = $this->getCollege()->getObservationForYear($this->getYear());

        return $observation;
    }
}
