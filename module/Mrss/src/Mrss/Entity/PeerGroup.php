<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/** @ORM\Entity
 * @ORM\Table(name="peer_groups")
 */
class PeerGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $year;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @var College
     */
    protected $college;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="text") */
    protected $states;

    /** @ORM\Column(type="string") */
    protected $environments;

    /** @ORM\Column(type="string") */
    protected $facultyUnionized;

    /** @ORM\Column(type="string") */
    protected $staffUnionized;

    /** @ORM\Column(type="string") */
    protected $workforceEnrollment;

    /** @ORM\Column(type="string") */
    protected $workforceRevenue;

    /** @ORM\Column(type="string") */
    protected $serviceAreaPopulation;

    /** @ORM\Column(type="string") */
    protected $serviceAreaUnemployment;

    /** @ORM\Column(type="string") */
    protected $serviceAreaMedianIncome;

    /** @ORM\Column(type="text") */
    protected $benchmarks;

    /** @ORM\Column(type="text") */
    protected $peers;

    /**
     * @param mixed $environments
     * @return $this
     */
    public function setEnvironments($environments)
    {
        $this->environments = $environments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    public function setFacultyUnionized($facultyUnionized)
    {
        $this->facultyUnionized = $facultyUnionized;

        return $this;
    }

    public function getFacultyUnionized()
    {
        return $this->facultyUnionized;
    }

    public function setStaffUnionized($staffUnionized)
    {
        $this->staffUnionized = $staffUnionized;

        return $this;
    }

    public function getStaffUnionized()
    {
        return $this->staffUnionized;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setCollege($college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param integer $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $serviceAreaMedianIncome
     * @return $this
     */
    public function setServiceAreaMedianIncome($serviceAreaMedianIncome)
    {
        $this->serviceAreaMedianIncome = $serviceAreaMedianIncome;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getServiceAreaMedianIncome($type = 'range')
    {
        $income = $this->serviceAreaMedianIncome;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($income);

            $income = $range[$type];
        }

        return $income;

    }

    /**
     * @param mixed $serviceAreaPopulation
     * @return $this
     */
    public function setServiceAreaPopulation($serviceAreaPopulation)
    {
        $this->serviceAreaPopulation = $serviceAreaPopulation;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getServiceAreaPopulation($type = 'range')
    {
        $population = $this->serviceAreaPopulation;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($population);

            $population = $range[$type];
        }

        return $population;

    }

    /**
     * @param mixed $serviceAreaUnemployment
     * @return $this
     */
    public function setServiceAreaUnemployment($serviceAreaUnemployment)
    {
        $this->serviceAreaUnemployment = $serviceAreaUnemployment;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getServiceAreaUnemployment($type = 'range')
    {
        $unemployment = $this->serviceAreaUnemployment;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($unemployment);

            $unemployment = $range[$type];
        }

        return $unemployment;
    }

    /**
     * @param array $states
     * @return $this
     */
    public function setStates($states)
    {
        $states = implode('|', $states);

        $this->states = $states;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStates()
    {
        $states = explode('|', $this->states);

        if ($states[0] == '') {
            $states = array();
        }

        return $states;
    }

    /**
     * @param mixed $workforceEnrollment
     * @return $this
     */
    public function setWorkforceEnrollment($workforceEnrollment)
    {
        $this->workforceEnrollment = $workforceEnrollment;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getWorkforceEnrollment($type = 'range')
    {
        $enrollment = $this->workforceEnrollment;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($enrollment);

            $enrollment = $range[$type];
        }

        return $enrollment;
    }

    /**
     * @param mixed $workforceRevenue
     * @return $this
     */
    public function setWorkforceRevenue($workforceRevenue)
    {
        $this->workforceRevenue = $workforceRevenue;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getWorkforceRevenue($type = 'range')
    {
        $revenue =  $this->workforceRevenue;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($revenue);

            $revenue = $range[$type];
        }

        return $revenue;
    }

    public function setBenchmarks($benchmarks)
    {
        $this->benchmarks = implode('|', $benchmarks);

        return $this;
    }

    public function getBenchmarks()
    {
        if ($this->benchmarks) {
            $benchmarks = explode('|', $this->benchmarks);
        } else {
            $benchmarks = array();
        }

        return $benchmarks;
    }

    public function setPeers($peers)
    {
        $this->peers = implode('|', $peers);

        return $this;
    }

    public function getPeers()
    {
        if ($this->peers) {
            $peers = explode('|', $this->peers);
        } else {
            $peers = array();
        }

        return $peers;
    }

    public function parseRange($range)
    {
        $parts = explode('-', $range);
        $min = intval(trim($parts[0]));
        $max = intval(trim($parts[1]));

        return array(
            'min' => $min,
            'max' => $max
        );
    }

    public function hasCriteria()
    {
        return (
            $this->getStates() ||
            $this->getEnvironments() ||
            $this->getWorkforceEnrollment() ||
            $this->getWorkforceRevenue() ||
            $this->getServiceAreaPopulation() ||
            $this->getServiceAreaUnemployment() ||
            $this->getServiceAreaMedianIncome() ||
            $this->getFacultyUnionized() ||
            $this->getStaffUnionized()
        );
    }
}
