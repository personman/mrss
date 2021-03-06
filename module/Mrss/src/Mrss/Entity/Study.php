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
     * @ORM\OneToMany(targetEntity="Criterion", mappedBy="study")
     * @ORM\OrderBy({"sequence" = "ASC"})
     * @var \Mrss\Entity\Criterion[]
     */
    protected $criteria;

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

    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="study")
     * @var Section[]
     */
    protected $sections;


    public function __construct()
    {
        $this->benchmarkGroups = new ArrayCollection();
        $this->offerCodes = new ArrayCollection();
        $this->sections = new ArrayCollection();
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
     * @return BenchmarkGroup[]
     */
    public function getBenchmarkGroups()
    {
        return $this->benchmarkGroups;
    }

    public function getBenchmarkGroupsBySubscription(Subscription $subscription)
    {
        if ($this->hasSections()) {
            $benchmarkGroupIds = $subscription->getBenchmarkGroupIds();

            $benchmarkGroups = array();
            foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
                if (!$benchmarkGroup->hasBenchmarksForYear($subscription->getYear())) {
                    if (!$benchmarkGroup->getShowWhenEmpty()) {
                        continue;
                    }
                }

                if (in_array($benchmarkGroup->getId(), $benchmarkGroupIds)) {
                    $benchmarkGroups[] = $benchmarkGroup;
                }
            }
        } else {
            $benchmarkGroups = $this->getBenchmarkGroups();
        }

        return $benchmarkGroups;
    }

    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * @return Criterion[]
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setOfferCodes($offerCodes)
    {
        $this->offerCodes = $offerCodes;

        return $this;
    }

    /** @return OfferCode[] */
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
    public function getSubscriptionsForYear($year = null)
    {
        if (!$year) {
            $year = $this->getCurrentYear();
        }

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

    /**
     * If it's before the early bird date, return the early price.
     * If it's after, return the normal price. In any case, ignore the year.
     */
    public function getCurrentPrice($renewal = false, $selectedSections = null)
    {
        // Build the base price
        if ($renewal) {
            $price = $this->getRenewalPrice();
        } elseif ($this->isEarlyBirdValid()) {
            $price = $this->getEarlyPrice();
        } else {
            $price = $this->getPrice();
        }

        if ($this->hasSections()) {
            $price += $this->getSectionPriceAddOn($selectedSections);
        }

        return $price;
    }

    public function getSectionPriceAddOn($selectedSections)
    {
        $price = $this->getSectionPrice($selectedSections);

        return $price;
    }

    public function getSectionPrice($selectedSections = array())
    {
        $useComboPricing = (count($selectedSections) > 1);
        $addOn = 0;

        // Add section pricing
        foreach ($selectedSections as $sectionId) {
            $section = $this->getSection($sectionId);

            if ($useComboPricing) {
                $addOn += $section->getComboPrice();
            } else {
                $addOn += $section->getPrice();
            }
        }

        return $addOn;
    }

    public function isEarlyBirdValid()
    {
        $deadline = $this->getEarlyPriceDateThisYear();
        $now = new \DateTime('now');

        return ($deadline > $now);
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

        /*if ($observation->getYear() == 2010) {
            pr($total); prd($completed);
        }*/
        if ($total > 0) {
            $percentage = ($completed / $total * 100);
            $percentage = round($percentage, 1);
        } else {
            $percentage = 0;
        }

        //pr($total); pr($completed); pr($percentage);

        return $percentage;
    }

    public function getDbColumnsIncludedInCompletion()
    {
        $dbColumns = array();

        foreach ($this->getBenchmarkGroups() as $group) {
            foreach ($group->getBenchmarksForCompletionCalculationForYear($this->getCurrentYear()) as $benchmark) {
                $dbColumns[] = $benchmark->getDbColumn();
            }
        }

        return $dbColumns;
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

    /**
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getAllBenchmarks()
    {
        $allBenchmarks = array();
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            $benchmarks = $benchmarkGroup->getBenchmarks();
            foreach ($benchmarks as $benchmark) {
                $allBenchmarks[$benchmark->getDbColumn()] = $benchmark;
            }
        }

        return $allBenchmarks;
    }

    public function getBenchmarksByInputType()
    {
        $byType = array();
        foreach ($this->getAllBenchmarks() as $benchmark) {
            $type = $benchmark->getInputType();
            /*if (empty($byType[$type])) {
                $byType[$type] = array();
            }*/

            $byType[$benchmark->getDbColumn()] = $benchmark->getInputType();
        }

        return $byType;
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

    public function getStructuredBenchmarks(
        $onlyReported = true,
        $keyField = 'dbColumn',
        $subscription = null,
        $onlyComputed = false
    ) {
        if ($subscription && $this->hasSections()) {
            $groups = $this->getBenchmarkGroupsBySubscription($subscription);
        } else {
            $groups = $this->getBenchmarkGroups();
        }

        $benchmarks = array();
        foreach ($groups as $benchmarkGroup) {
            $group = array(
                'label' => $benchmarkGroup->getName(),
                'options' => array()
            );

            foreach ($benchmarkGroup->getBenchmarksForYear($this->getCurrentYear()) as $benchmark) {
                // Skip non-report benchmarks
                if ($onlyReported && !$benchmark->getIncludeInNationalReport()) {
                    continue;
                }

                // Skip non-computed benchmarks
                if ($onlyComputed && !$benchmark->getComputed()) {
                    continue;
                }

                $key = $benchmark->getDbColumn();
                if ($keyField == 'id') {
                    $key = $benchmark->getId();
                }

                $group['options'][$key] = $benchmark->getDescriptiveReportLabel();
            }

            $benchmarks[$benchmarkGroup->getId()] = $group;
        }

        return $benchmarks;
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

    public function hasSubobservations()
    {
        $has = false;
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            if ($benchmarkGroup->getUseSubObservation()) {
                $has = true;
                break;
            }
        }

        return $has;
    }

    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function hasSections()
    {
        return (count($this->getSections()) > 0);
    }

    public function getSection($sectionId)
    {
        foreach ($this->getSections() as $section) {
            if ($section->getId() == $sectionId) {
                return $section;
            }
        }
    }
}
