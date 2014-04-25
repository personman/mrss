<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\User;
use Mrss\Entity\Change;
use Mrss\Entity\Observation;
use Mrss\Entity\Study;

/**
 * Entity to track Observation changes
 *
 * @ORM\Entity
 * @ORM\Table(name="change_sets")
 */
class ChangeSet {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Observation", inversedBy="changes")
     * @var Observation
     */
    protected $observation;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     * @var Study
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="changes", nullable=true)
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User", nullable=true)
     * @var User
     */
    protected $impersonatingUser;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $date;

    /**
     * @ORM\OneToMany(targetEntity="Change", mappedBy="changeSet")
     */
    protected $changes;


    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setObservation(Observation $observation)
    {
        $this->observation = $observation;

        return $this;
    }

    public function getObservation()
    {
        return $this->observation;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
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

    public function setImpersonatingUser(User $user)
    {
        $this->impersonatingUser = $user;
    }

    /**
     * @return User|null
     */
    public function getImpersonatingUser()
    {
        return $this->impersonatingUser;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param Change[] $changes
     * @return $this
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;

        return $this;
    }

    public function getChanges()
    {
        return $this->changes;
    }
}
