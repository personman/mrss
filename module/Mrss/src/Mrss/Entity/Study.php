<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;

/**
 * Study/project
 *
 * Groups of benchmarkGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="studies")
 */
class Study
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $currentYear;

    /**
     * @ORM\OneToMany(targetEntity="BenchmarkGroup", mappedBy="study")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $benchmarkGroups;

    /**
     * @ORM\Column(type="float")
     */
    protected $price;

    /**
     * @ORM\Column(type="float")
     */
    protected $earlyPrice;

    /**
     * @ORM\Column(type="date")
     */
    protected $earlyPriceDate;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enrollmentOpen;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pilotOpen;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $dataEntryOpen;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $reportsOpen;

    /**
     * @ORM\Column(type="string")
     */
    protected $uPayUrl;

    /**
     * @ORM\Column(type="integer")
     */
    protected $uPaySiteId;

    /**
     * @ORM\Column(type="string")
     */
    protected $logo;



    public function __construct()
    {
        $this->benchmarkGroups = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setBenchmarkGroups($benchmarkGroups)
    {
        $this->benchmarkGroups = $benchmarkGroups;

        return $this;
    }

    public function getBenchmarkGroups()
    {
        return $this->benchmarkGroups;
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

    /**
     * If it's before the early bird date, return the early price.
     * If it's after, return the normal price. In any case, ignore the year.
     */
    public function getCurrentPrice()
    {
        $deadline = $this->getEarlyPriceDateThisYear();
        $now = new \DateTime('now');

        if ($deadline > $now) {
            $price = $this->getEarlyPrice();
        } else {
            $price = $this->getPrice();
        }

        return $price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setEarlyPrice($earlyPrice)
    {
        $this->earlyPrice = $earlyPrice;

        return $this;
    }

    public function getEarlyPrice()
    {
        return $this->earlyPrice;
    }

    public function setEarlyPriceDate($date)
    {
        $this->earlyPriceDate = $date;

        return $this;
    }

    public function getEarlyPriceDate()
    {
        return $this->earlyPriceDate;
    }

    public function getEarlyPriceDateThisYear()
    {
        // Set the year to the current year.
        $earlyBirdDeadline = $this->getEarlyPriceDate();
        $thisYear = date('Y');
        $month = $earlyBirdDeadline->format('m');
        $day = $earlyBirdDeadline->format('d');
        $earlyBirdDeadline->setDate($thisYear, $month, $day);

        return $earlyBirdDeadline;
    }

    public function setPilotOpen($pilotOpen)
    {
        $this->pilotOpen = $pilotOpen;

        return $this;
    }

    public function getPilotOpen()
    {
        return $this->pilotOpen;
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

    public function setUPayUrl($uPayUrl)
    {
        $this->uPayUrl = $uPayUrl;

        return $this;
    }

    public function getUPayUrl()
    {
        return $this->uPayUrl;
    }

    public function setUPaySiteId($uPaySiteId)
    {
        $this->uPaySiteId = $uPaySiteId;

        return $this;
    }

    public function getUPaySiteId()
    {
        return $this->uPaySiteId;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function getCompletionPercentage(Observation $observation)
    {
        $total = 0;
        $completed = 0;

        // Loop over each benchmarkGroup and sum up the counts
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            $total += count(
                $benchmarkGroup->getBenchmarksForYear($observation->getYear())
            );
            $completed += $benchmarkGroup
                ->countCompleteFieldsInObservation($observation);
        }

        if ($total > 0) {
            $percentage = ($completed / $total * 100);
            $percentage = round($percentage, 1);
        } else {
            $percentage = 0;
        }

        return $percentage;
    }

    /**
     * Get the benchmarks for the current year and return all of their input filters
     */
    public function getInputFilter()
    {
        $inputFilter = new InputFilter();

        foreach ($this->getBenchmarkGroups() as $group) {
            $year = $this->getCurrentYear();
            foreach ($group->getNonComputedBenchmarksForYear($year) as $benchmark) {
                $inputFilter->add($benchmark->getFormElementInputFilter());
            }
        }

        return $inputFilter;
    }
}
