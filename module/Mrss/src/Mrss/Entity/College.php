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
    protected $opeId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", nullable=true)
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
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $execTitle;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $execSalutation;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $execFirstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $execMiddleName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $execLastName;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $execEmail;

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
     * @ORM\OrderBy({"year" = "DESC"})
     */
    protected $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity="PeerGroup", mappedBy="college")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $peerGroups;

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

    public function getOpeId()
    {
        return $this->opeId;
    }

    public function setOpeId($opeId)
    {
        $this->opeId = $opeId;

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

    public function setExecTitle($title)
    {
        $this->execTitle = $title;

        return $this;
    }

    public function getExecTitle()
    {
        return $this->execTitle;
    }

    public function setExecSalutation($salutation)
    {
        $this->execSalutation = $salutation;

        return $this;
    }

    public function getExecSalutation()
    {
        return $this->execSalutation;
    }

    public function setExecFirstName($name)
    {
        $this->execFirstName = $name;

        return $this;
    }

    public function getExecFirstName()
    {
        return $this->execFirstName;
    }

    public function setExecMiddleName($name)
    {
        $this->execMiddleName = $name;

        return $this;
    }

    public function getExecMiddleName()
    {
        return $this->execMiddleName;
    }

    public function setExecLastName($name)
    {
        $this->execLastName = $name;

        return $this;
    }

    public function getExecLastName()
    {
        return $this->execLastName;
    }

    public function setExecEmail($email)
    {
        $this->execEmail = $email;

        return $this;
    }

    public function getExecEmail()
    {
        return $this->execEmail;
    }

    public function getExecFullName()
    {
        $name = $this->getExecSalutation();
        $name .= ' ' . $this->getExecFirstName();

        if ($middle = $this->getExecMiddleName()) {
            $name .= ' ' . $middle;
        }

        $name .= ' ' . $this->getExecLastName();

        return $name;
    }
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|Observation[]
     */
    public function getObservations()
    {
        return $this->observations;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Mrss\Entity\User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param Study $study
     * @return \Doctrine\Common\Collections\ArrayCollection|\Mrss\Entity\User[]
     */
    public function getUsersByStudy(Study $study)
    {
        $users = array();
        foreach ($this->getUsers() as $user) {
            if ($user->hasStudy($study)) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function getDataEmails()
    {
        $emails = array();
        foreach ($this->getDataUsers() as $user) {
            $emails[] = $user->getEmail();
        }

        return implode(',', $emails);
    }

    /**
     * @return \Mrss\Entity\User[]
     */
    public function getDataUsers($study = null)
    {
        $dataRoles = array('data', 'system_admin');

        if ($study) {
            $users = $this->getUsersByStudy($study);
        } else {
            $users = $this->getUsers();
        }

        $dataUsers = array();
        foreach ($users as $user) {
            if (in_array($user->getRole(), $dataRoles)) {
                $dataUsers[] = $user;
            }
        }

        return $dataUsers;
    }

    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Subscription[]
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function setPeerGroups($peerGroups)
    {
        $this->peerGroups = $peerGroups;

        return $this;
    }

    /**
     * @return \Mrss\Entity\PeerGroup[]
     */
    public function getPeerGroups()
    {
        return $this->peerGroups;
    }

    /**
     * @param Study $study
     * @return Subscription[]
     */
    public function getSubscriptionsForStudy(Study $study)
    {
        $subscriptions = array();
        foreach ($this->getSubscriptions() as $sub) {
            if (!$subStudy = $sub->getStudy()) {
                //$m = "Subscription ({$sub->getId()}) missing study";
                //pr($m);
                continue;

                //var_dump($s);
                //throw new \Exception($m);
            }

            $subStudy = $sub->getStudy()->getId();
            $study->getId();

            if ($sub->getStudy()->getId() == $study->getId()) {
                $subscriptions[$sub->getYear()] = $sub;
            }
        }

        return $subscriptions;
    }

    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * @return null|System
     */
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

    public function getNameAndState()
    {
        return $this->getName() . ' (' . $this->getState() . ')';
    }

    public function getSubscriptionsForYear($year)
    {
        $subscriptions = $this->getSubscriptions();
        $subscriptionsForYear = array();

        foreach ($subscriptions as $subscription) {
            if ($year == $subscription->getYear()) {
                $subscriptionsForYear[] = $subscription;
            }
        }

        return $subscriptionsForYear;
    }

    /**
     * @param Study $study
     * @param bool $skipCurrentYearIfReportsClosed
     * @return array
     * @deprecated This method was causing errors in peer comparison
     */
    public function getYearsWithSubscriptions(Study $study, $skipCurrentYearIfReportsClosed = true)
    {
        $years = array();
        foreach ($this->getSubscriptionsForStudy($study) as $sub) {
            if ($skipCurrentYearIfReportsClosed) {
                if ($sub->getYear() == $study->getCurrentYear() && !$study->getReportsOpen()) {
                    continue;
                }
            }

            $years[] = $sub->getYear();
        }

        rsort($years);

        return $years;
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
