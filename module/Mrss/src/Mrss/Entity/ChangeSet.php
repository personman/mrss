<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity to track Observation changes
 *
 * @ORM\Entity
 * @ORM\Table(name="change_sets")
 */
class ChangeSet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * Phasing this out in favor of subscriptions
     *
     * @ORM\ManyToOne(targetEntity="Observation", inversedBy="changes")
     * @ORM\JoinColumn(name="observation_id", referencedColumnName="id", onDelete="SET NULL")
     * @var Observation
     */
    protected $observation;

    /**
     * @ORM\ManyToOne(targetEntity="SubObservation")
     * @ORM\JoinColumn(name="subobservation_id", referencedColumnName="id", onDelete="SET NULL")
     * @var SubObservation
     */
    protected $subObservation;

    /**
     * @ORM\ManyToOne(targetEntity="Subscription", inversedBy="changes")
     * @ORM\JoinColumn(name="subscription_id", referencedColumnName="id", onDelete="SET NULL")
     * @var Subscription
     */
    protected $subscription;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     * @var Study
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="changes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="impersonatingUser_id", referencedColumnName="id", onDelete="SET NULL")
     * @var User
     */
    protected $impersonatingUser;

    /**
     * @ORM\Column(type="text")
     */
    protected $editType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\OneToMany(targetEntity="Change", mappedBy="changeSet",cascade={"persist", "remove"})
     * @var ArrayCollection|Change[]
     */
    protected $changes;

    public function __construct()
    {
        $this->changes = new ArrayCollection;
    }

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

    public function setSubObservation(SubObservation $subObservation)
    {
        $this->subObservation = $subObservation;

        return $this;
    }

    public function getSubObservation()
    {
        return $this->subObservation;
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

    public function setEditType($type)
    {
        $this->editType = $type;

        return $this;
    }

    public function getEditType()
    {
        return $this->editType;
    }

    public function getEditTypeLabel()
    {
        $type = $this->getEditType();

        $map = array(
            'dataEntry' => 'data entry form',
            'excel' => 'Excel upload',
            'adminEdit' => 'admin edit form'
        );

        if (!empty($map[$type])) {
            $type = $map[$type];
        }

        return $type;
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

    /**
     * Switching from observations to subscriptions as the connection point
     * @return Subscription
     */
    public function getSubscription()
    {
        $sub = $this->subscription;

        if (empty($sub)) {
            $sub = $this->getObservation()->getSubscription();
            $this->setSubscription($sub);
        }

        return $sub;
    }

    /**
     * @param Observation $subscription
     * @return ChangeSet
     */
    public function setSubscription($subscription = null)
    {
        $this->subscription = $subscription;
        return $this;
    }


}
