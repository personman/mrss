<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Steps for adding a demographic criteria
 *
 * 1) Add property, getter, setter in this file.
 * 2) Add new getter to the hasCriteria() method in this file.
 * 3) Generate and run migration to add db column.
 * 4) Add new field and inputFilter to Form/PeerComparisonDemographics.php.
 * 5) Add output for the new criteria to peer.phtml.
 * 6) Add new filter to college model's findByPeerGroup() method.
 *
 * @ORM\Entity
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

    /** @ORM\Column(type="text", nullable=true) */
    protected $states;

    /** @ORM\Column(type="string", nullable=true) */
    protected $environments;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutionalType;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutionalControl;

    /** @ORM\Column(type="string", nullable=true) */
    protected $facultyUnionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $staffUnionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $workforceEnrollment;

    /** @ORM\Column(type="string", nullable=true) */
    protected $workforceRevenue;

    /** @ORM\Column(type="string", nullable=true) */
    protected $ipedsFallEnrollment;

    /** @ORM\Column(type="string", nullable=true) */
    protected $fiscalCreditHours;

    /** @ORM\Column(type="string", nullable=true) */
    protected $pellGrantRecipients;

    /** @ORM\Column(type="string", nullable=true) */
    protected $blk;

    /** @ORM\Column(type="string", nullable=true) */
    protected $asian;

    /** @ORM\Column(type="string", nullable=true) */
    protected $hispAnyrace;

    /** @ORM\Column(type="string", nullable=true) */
    protected $operatingRevenue;

    /** @ORM\Column(type="string", nullable=true) */
    protected $serviceAreaPopulation;

    /** @ORM\Column(type="string", nullable=true) */
    protected $serviceAreaUnemployment;

    /** @ORM\Column(type="string", nullable=true) */
    protected $serviceAreaMedianIncome;

    /** @ORM\Column(type="string", nullable=true) */
    protected $technicalCredit;

    /** @ORM\Column(type="string", nullable=true) */
    protected $percentageFullTime;

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
        $environments = implode('|', $environments);

        $this->environments = $environments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnvironments()
    {
        $environments = explode('|', $this->environments);

        if ($environments[0] == '') {
            $environments = array();
        }

        return $environments;
    }
    
    public function setInstitutionalControl($control)
    {
        $control = implode('|', $control);

        $this->institutionalControl = $control;
        
        return $this;
    }
    
    public function getInstitutionalControl()
    {
        $control = explode('|', $this->institutionalControl);

        if ($control[0] == '') {
            $control = array();
        }

        return $control;
    }
    
    public function setInstitutionalType($type)
    {
        $type = implode('|', $type);

        $this->institutionalType = $type;

        return $this;
    }
    
    public function getInstitutionalType()
    {
        $type = explode('|', $this->institutionalType);

        if ($type[0] == '') {
            $type = array();
        }

        return $type;
    }

    public function setFacultyUnionized($facultyUnionized)
    {
        $facultyUnionized = implode('|', $facultyUnionized);

        $this->facultyUnionized = $facultyUnionized;

        return $this;
    }

    public function getFacultyUnionized()
    {
        $unionized = explode('|', $this->facultyUnionized);

        if ($unionized[0] == '') {
            $unionized = array();
        }

        return $unionized;
    }

    public function setStaffUnionized($staffUnionized)
    {
        $staffUnionized = implode('|', $staffUnionized);

        $this->staffUnionized = $staffUnionized;

        return $this;
    }

    public function getStaffUnionized()
    {
        $unionized = explode('|', $this->staffUnionized);

        if ($unionized[0] == '') {
            $unionized = array();
        }

        return $unionized;
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
     * @param $technicalCredit
     * @return $this
     */
    public function setTechnicalCredit($technicalCredit)
    {
        $this->technicalCredit = $technicalCredit;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getTechnicalCredit($type = 'range')
    {
        $technical = $this->technicalCredit;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($technical);

            $technical = $range[$type];
        }

        return $technical;
    }

    /**
     * @param $percentage
     * @return $this
     */
    public function setPercentageFullTime($percentage)
    {
        $this->percentageFullTime = $percentage;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getPercentageFullTime($type = 'range')
    {
        $percentage = $this->percentageFullTime;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($percentage);

            $percentage = $range[$type];
        }

        return $percentage;
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
     * @param $pell
     * @return $this
     */
    public function setPellGrantRecipients($pell)
    {
        $this->pellGrantRecipients = $pell;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getPellGrantRecipients($type = 'range')
    {
        $pell = $this->pellGrantRecipients;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($pell);

            $pell = $range[$type];
        }

        return $pell;
    }

    /**
     * @param $blk
     * @return $this
     */
    public function setBlk($blk)
    {
        $this->blk = $blk;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getBlk($type = 'range')
    {
        $blk = $this->blk;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($blk);

            $blk = $range[$type];
        }

        return $blk;
    }

    /**
     * @param $asian
     * @return $this
     */
    public function setAsian($asian)
    {
        $this->asian = $asian;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getAsian($type = 'range')
    {
        $asian = $this->asian;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($asian);

            $asian = $range[$type];
        }

        return $asian;
    }

    /**
     * @param $hispanyrace
     * @return $this
     */
    public function setHispAnyrace($hispanyrace)
    {
        $this->hispAnyrace = $hispanyrace;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getHispAnyrace($type = 'range')
    {
        $hispanyrace = $this->hispAnyrace;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($hispanyrace);

            $hispanyrace = $range[$type];
        }

        return $hispanyrace;
    }

    /**
     * @param $revenue
     * @return $this
     */
    public function setOperatingRevenue($revenue)
    {
        $this->operatingRevenue = $revenue;

        return $this;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getOperatingRevenue($type = 'range')
    {
        $revenue = $this->operatingRevenue;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($revenue);

            $revenue = $range[$type];
        }

        return $revenue;
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

    public function setIpedsFallEnrollment($enrollment)
    {
        $this->ipedsFallEnrollment = $enrollment;

        return $this;
    }

    public function getIpedsFallEnrollment($type = 'range')
    {
        $enrollment = $this->ipedsFallEnrollment;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($enrollment);

            $enrollment = $range[$type];
        }

        return $enrollment;
    }

    public function setFiscalCreditHours($hours)
    {
        $this->fiscalCreditHours = $hours;

        return $this;
    }

    public function getFiscalCreditHours($type = 'range')
    {
        $hours = $this->fiscalCreditHours;

        if (in_array($type, array('min', 'max'))) {
            $range = $this->parseRange($hours);

            $hours = $range[$type];
        }

        return $hours;
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
            $this->getTechnicalCredit() ||
            $this->getPercentageFullTime() ||
            $this->getFacultyUnionized() ||
            $this->getStaffUnionized() ||
            $this->getInstitutionalControl() ||
            $this->getInstitutionalType() ||
            $this->getBlk() ||
            $this->getAsian() ||
            $this->getHispAnyrace()
        );
    }
}
