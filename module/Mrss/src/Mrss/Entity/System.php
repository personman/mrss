<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
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
     * @return mixed
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
     * @return mixed
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
     */
    public function getAdmins($role = 'system_admin')
    {
        $systemAdmins = array();

        foreach ($this->getColleges() as $college) {
            foreach ($college->getUsers() as $user) {
                if ($user->getRole() == $role) {
                    $systemAdmins[] = $user;
                }
            }
        }

        return $systemAdmins;
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
}
