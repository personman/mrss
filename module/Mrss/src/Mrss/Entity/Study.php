<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var \Mrss\Entity\BenchmarkGroup[]
     */
    protected $benchmarkGroups;

    /**
     * @ORM\Column(type="float")
     */
    protected $price;

    /**
     * @ORM\Column(type="float")
     */
    protected $renewalPrice;

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
    protected $outlierReportsOpen;

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

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $googleAnalyticsKey;

    /**
     * @ORM\OneToMany(targetEntity="OfferCode", mappedBy="study")
     * @ORM\OrderBy({"code" = "ASC"})
     */
    protected $offerCodes;

    /**
     * @ORM\OneToMany(targetEntity="Subscription", mappedBy="study")
     * @var Subscription[]
     */
    protected $subscriptions;

    public function __construct()
    {
        $this->benchmarkGroups = new ArrayCollection();
        $this->offerCodes = new ArrayCollection();
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

    /**
     * @return \Mrss\Entity\BenchmarkGroup[]
     */
    public function getBenchmarkGroups()
    {
        return $this->benchmarkGroups;
    }

    public function setOfferCodes($offerCodes)
    {
        $this->offerCodes = $offerCodes;

        return $this;
    }

    /** @return \Mrss\Entity\OfferCode[] */
    public function getOfferCodes()
    {
        return $this->offerCodes;
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

    /**
     * @param $year
     * @return \Mrss\Entity\Subscription[]
     */
    public function getSubscriptionsForYear($year)
    {
        $subscriptions = $this->getSubscriptions();
        $subscriptionsForYear = array();

        foreach ($subscriptions as $subscription) {
            if ($subscription->getYear() == $year) {
                $subscriptionsForYear[] = $subscription;
            }
        }

        return $subscriptionsForYear;
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

    public function getCurrentYearMinus($minus)
    {
        $minus = intval($minus);

        return $this->currentYear - $minus;
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

    public function setRenewalPrice($price)
    {
        $this->renewalPrice = $price;

        return $this;
    }

    public function getRenewalPrice()
    {
        return $this->renewalPrice;
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

    public function getEarlyPriceDateForStudyYear()
    {
        // Set the year to the current year.
        $earlyBirdDeadline = $this->getEarlyPriceDate();
        $thisYear = $this->getCurrentYear();
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

    public function setOutlierReportsOpen($reportsOpen)
    {
        $this->outlierReportsOpen = $reportsOpen;
    }

    public function getOutlierReportsOpen()
    {
        return $this->outlierReportsOpen;
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

    public function setGoogleAnalyticsKey($key)
    {
        $this->googleAnalyticsKey = $key;

        return $this;
    }

    public function getGoogleAnalyticsKey()
    {
        return $this->googleAnalyticsKey;
    }

    public function hasOfferCode()
    {
        $codes = $this->getOfferCodes();

        return (!empty($this->offerCodes));
    }

    /**
     * Check to see if the supplied offer code matches any current codes
     * (case insensitive)
     *
     * @param $code
     * @return bool
     */
    public function checkOfferCode($code)
    {
        $codes = $this->getOfferCodesArray();

        // Make it case-insensitive
        $code = strtolower($code);
        $codes = array_map('strtolower', $codes);

        return (in_array($code, $codes));
    }

    public function getOfferCodesArray()
    {
        $codes = array();
        $offerCodes = $this->getOfferCodes();

        foreach ($offerCodes as $offerCode) {
            $codes[] = trim($offerCode->getCode());
        }

        return $codes;
    }

    public function getOfferCode($code)
    {
        $offers = $this->getOfferCodes();
        foreach ($offers as $offer) {
            if (strtolower($offer->getCode()) == strtolower($code)) {
                return $offer;
            }
        }

        return null;
    }

    public function getOfferCodePrice($code)
    {
        foreach ($this->getOfferCodes() as $offerCode) {
            $code = strtolower(trim($code));
            $validCode = strtolower(trim($offerCode->getCode()));
            if ($code == $validCode) {
                return $offerCode->getPrice();
            }
        }

        // If none match, return the normal price
        return $this->getCurrentPrice();
    }

    public function getCompletionPercentage(Observation $observation)
    {
        $total = 0;
        $completed = 0;

        // Loop over each benchmarkGroup and sum up the counts
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            $total += $benchmarkGroup->getBenchmarkCount($observation);
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

    public function getBenchmarksForYear($year)
    {
        $allBenchmarks = array();
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
            $allBenchmarks = array_merge($allBenchmarks, $benchmarks);
        }

        return $allBenchmarks;
    }

    public function getAllBenchmarkKeys()
    {
        $allKeys = array();
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            foreach ($benchmarkGroup->getBenchmarks() as $benchmark) {
                $allKeys[] = $benchmark->getDbColumn();
            }
        }

        return $allKeys;
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
