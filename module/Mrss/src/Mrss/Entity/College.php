<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/** @ORM\Entity
 * @ORM\Table(name="colleges")
 */
class College
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $ipeds;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $city;

        /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    protected $zip;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $longitude;

    /**
     * @ORM\OneToMany(targetEntity="Observation", mappedBy="college")
     * @ORM\OrderBy({"year" = "ASC"})
     */
    protected $observations;

    /**
     * @ORM\OneToMany(targetEntity="Subscription", mappedBy="college")
     * @ORM\OrderBy({"year" = "ASC"})
     */
    protected $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="college")
     */
    protected $users;

    /**
     * @ORM\ManyToOne(targetEntity="System", inversedBy="colleges")
     */
    protected $system;

    /**
     * Construct the college entity
     * Populate the observations property with a placeholder
     */
    public function __construct()
    {
        $this->observations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getIpeds()
    {
        return $this->ipeds;
    }

    public function setIpeds($ipeds)
    {
        $this->ipeds = $ipeds;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address)
    {
        $this->address2 = $address;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    public function getObservations()
    {
        return $this->observations;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    public function getSystem()
    {
        return $this->system;
    }

    public function getFullAddress()
    {
        $address = $this->getAddress() . "<br>\n"
            . $this->getCity() . ", "
            . $this->getState() . " "
            . $this->getZip();

        return $address;
    }

    public function getObservationForYear($year)
    {
        $observations = $this->getObservations();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('year', $year));

        $observations = $observations->matching($criteria);

        // We just want one
        if ($observations->count() > 0) {
            $observation = $observations->first();
        } else {
            $observation = null;
        }

        return $observation;
    }

    public function getCompletionPercentage($year, Study $study)
    {
        $observation = $this->getObservationForYear($year);

        if (empty($observation)) {
            return 0;
        }

        return $study->getCompletionPercentage($observation);
    }

    public function getSubscriptionByStudyAndYear($studyId, $year)
    {
        foreach ($this->getSubscriptions() as $subscription) {
            if ($subscription->getYear() == $year) {
                if ($subscription->getStudy()->getId() == $studyId) {
                    return $subscription;
                }
            }
        }

        return false;
    }
}
