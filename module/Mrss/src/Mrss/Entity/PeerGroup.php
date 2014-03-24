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

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="text") */
    protected $states;

    /** @ORM\Column(type="string") */
    protected $environments;

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

    /**
     * @param mixed $environments
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

    /**
     * @param mixed $id
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

    /**
     * @param integer $year
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
     */
    public function setServiceAreaMedianIncome($serviceAreaMedianIncome)
    {
        $this->serviceAreaMedianIncome = $serviceAreaMedianIncome;

        return $this;
    }

    /**
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
     */
    public function setServiceAreaPopulation($serviceAreaPopulation)
    {
        $this->serviceAreaPopulation = $serviceAreaPopulation;

        return $this;
    }

    /**
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
     */
    public function setServiceAreaUnemployment($serviceAreaUnemployment)
    {
        $this->serviceAreaUnemployment = $serviceAreaUnemployment;

        return $this;
    }

    /**
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
     */
    public function setWorkforceEnrollment($workforceEnrollment)
    {
        $this->workforceEnrollment = $workforceEnrollment;

        return $this;
    }

    /**
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
     */
    public function setWorkforceRevenue($workforceRevenue)
    {
        $this->workforceRevenue = $workforceRevenue;

        return $this;
    }

    /**
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
            $this->getServiceAreaMedianIncome()
        );
    }
}
