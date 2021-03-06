<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Doctrine\Common\Collections\Criteria;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/** @ORM\Entity
 * @ORM\Table(name="college_systems")
 */
class System
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ipeds = null;

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
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    protected $joinSetting;

    /**
     * @ORM\OneToMany(targetEntity="SystemMembership", mappedBy="system")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $memberships;

    /**
     * @ORM\OneToOne(targetEntity="Structure", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="dataEntryStructure_id", referencedColumnName="id", nullable=true)
     */
    protected $dataEntryStructure = null;

    /**
     * @ORM\OneToOne(targetEntity="Structure", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="reportStructure_id", referencedColumnName="id", nullable=true)
     */
    protected $reportStructure = null;


    // Pushed down from the study entity:

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $currentYear;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $enrollmentOpen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $pilotOpen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $dataEntryOpen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $outlierReportsOpen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $reportsOpen;



    public function __construct()
    {
        $this->colleges = new ArrayCollection();
        $this->memberships = new ArrayCollection();
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

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
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

    /**
     * @return mixed
     */
    public function getJoinSetting()
    {
        return $this->joinSetting;
    }

    /**
     * @param mixed $joinSetting
     * @return System
     */
    public function setJoinSetting($joinSetting)
    {
        $this->joinSetting = $joinSetting;
        return $this;
    }



    public function setColleges($colleges)
    {
        $this->colleges = $colleges;

        return $this;
    }

    /**
     * @return College[]
     */
    public function getColleges()
    {
        $colleges = array();
        foreach ($this->getMemberships() as $membership) {
            $college = $membership->getCollege();
            $colleges[$college->getId()] = $college;
        }

        return array_values($colleges);
    }

    /**
     * @return null|SystemMembership[]
     */
    public function getRecentMemberships($year, $limit = 3)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('year', $year))
            ->setMaxResults($limit);

        $memberships = $this->getMemberships()->matching($criteria);

        return $memberships;
    }

    /**
     * @param $year
     * @return \Mrss\Entity\SystemMembership[]
     */
    public function getMembershipsByYear($year)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('year', $year));

        $memberships = $this->getMemberships()->matching($criteria);

        return $memberships;
    }

    /**
     * @return null|SystemMembership[]
     */
    public function getMemberships()
    {
        return $this->memberships;
    }

    /**
     * @param mixed $memberships
     * @return System
     */
    public function setMemberships($memberships)
    {
        $this->memberships = $memberships;
        return $this;
    }

    /**
     * @return \Mrss\Entity\Structure
     */
    public function getDataEntryStructure()
    {
        return $this->dataEntryStructure;
    }

    /**
     * @param mixed $dataEntryStructure
     * @return System
     */
    public function setDataEntryStructure($dataEntryStructure)
    {
        $this->dataEntryStructure = $dataEntryStructure;
        return $this;
    }

    /**
     * @return \Mrss\Entity\Structure
     */
    public function getReportStructure()
    {
        return $this->reportStructure;
    }

    /**
     * @param mixed $reportStructure
     * @return System
     */
    public function setReportStructure($reportStructure)
    {
        $this->reportStructure = $reportStructure;
        return $this;
    }


    /**
     * Return a list of system admins for this system
     * @return \Mrss\Entity\User[]
     */
    public function getAdmins($role = 'system_admin')
    {
        if ($role == 'system_viewer') {
            return $this->getSystemViewers();
        }


        $systemAdmins = array();

        // New way:
        foreach ($this->getColleges() as $college) {
            foreach ($college->getUsers() as $user) {
                if ($user->administersSystem($this->getId())) {
                    $systemAdmins[] = $user;
                }
            }
        }

        // Old way
        /*if (empty($systemAdmins)) {
            foreach ($this->getColleges() as $college) {
                foreach ($college->getUsers() as $user) {
                    if ($user->getRole() == $role) {
                        $systemAdmins[] = $user;
                    }
                }
            }
        }*/

        return $systemAdmins;
    }

    public function getSystemViewers()
    {
        $role = 'system_viewer';
        $systemViewers = array();

        if (empty($systemViewers)) {
            foreach ($this->getColleges() as $college) {
                foreach ($college->getUsers() as $user) {
                    if ($user->getRole() == $role) {
                        $systemViewers[] = $user;
                    }
                }
            }
        }

        return $systemViewers;
    }

    public function getChildren()
    {
        return array();
    }

    public function getViewers()
    {
        return $this->getAdmins('system_viewer');
    }

    public function getMemberColleges()
    {
        $memberships = $this->getMemberships();

        $colleges = array();
        foreach ($memberships as $membership) {
            $collegeId = $membership->getCollege()->getId();
            if (!isset($colleges[$collegeId])) {
                $colleges[$membership->getCollege()->getId()] = array(
                    'college' => $membership->getCollege(),
                    'years' => array()
                );
            }

            $colleges[$collegeId]['years'][] = $membership->getYear();
            sort($colleges[$collegeId]['years']);
        }

        return $colleges;
    }

    public function getInputFilter()
    {
        $inputFilter = new InputFilter();
        $factory = new InputFactory();

        $inputFilter->add(
            $factory->createInput(
                array(
                    'name' => 'name',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim')
                    ),
                    'validators' => array(
                        array('name' => 'NotEmpty')
                    )
                )
            )
        );

        /*$inputFilter->add(
            $factory->createInput(
                array(
                    'name' => 'ipeds',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim')
                    ),
                    'validators' => array(
                        array('name' => 'NotEmpty'),
                        array('name' => 'Digits')
                    )
                )
            )
        );*/

        return $inputFilter;
    }

    public function getSubscriptionsByStudyAndYear($studyId, $year)
    {
        $colleges = $this->getColleges();
        $subscriptions = array();
        foreach ($colleges as $college) {
            // Make sure there's a subscription
            $subscription = $college->getSubscriptionByStudyAndYear(
                $studyId,
                $year
            );

            if (!empty($subscription)) {
                $subscriptions[] = $subscription;
            }
        }

        return $subscriptions;
    }



    public function setCurrentYear($year)
    {
        $this->currentYear = $year;

        return $this;
    }

    public function getCurrentYear()
    {
        return $this->currentYear;
    }

    public function getLatestReportYear()
    {
        $year = $this->getCurrentYear();
        if (!$this->getReportsOpen()) {
            $year = $year - 1;
        }

        return $year;
    }

    public function getCurrentYearMinus($minus)
    {
        $minus = intval($minus);

        return $this->currentYear - $minus;
    }

    public function setEnrollmentOpen($enrollmentOpen)
    {
        $this->enrollmentOpen = $enrollmentOpen;

        return $this;
    }

    public function getEnrollmentOpen()
    {
        return $this->enrollmentOpen;
    }

    public function setDataEntryOpen($dataEntryOpen)
    {
        $this->dataEntryOpen = $dataEntryOpen;

        return $this;
    }

    public function getDataEntryOpen()
    {
        return $this->dataEntryOpen;
    }

    public function setReportsOpen($reportsOpen)
    {
        $this->reportsOpen = $reportsOpen;
    }

    public function getReportsOpen()
    {
        return $this->reportsOpen;
    }

    public function setOutlierReportsOpen($reportsOpen)
    {
        $this->outlierReportsOpen = $reportsOpen;
    }

    public function getOutlierReportsOpen()
    {
        return $this->outlierReportsOpen;
    }
}
