<?php

namespace Mrss\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Mrss\Entity\College;
use Mrss\Entity\Study;

/** @ORM\Entity
 * @ORM\Table(name="subscriptions")
 */
class Subscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="subscriptions")
     * @var College
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="subscriptions")
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="Observation", inversedBy="subscriptions")
     */
    protected $observation;

    /**
     * @ORM\OneToMany(targetEntity="Datum", mappedBy="subscription", cascade={"persist", "remove"})
     * @var Datum[]
     */
    protected $data;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentMethod;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $paymentAmount;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentSystemName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentAddress2;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentCity;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentState;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentZip;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentTransactionId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $digitalSignature;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $digitalSignatureTitle;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $completion;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="Suppression", mappedBy="subscription")
     */
    protected $suppressions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $reportAccess = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $paid = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paidNotes;

    /**
     * @ORM\ManyToMany(targetEntity="Section")
     * @ORM\JoinTable(name="subscription_sections")
     * @var \Mrss\Entity\Section[]
     */
    protected $sections;


    protected $benchmarkModel;
    protected $datumModel;
    protected $allData = array();

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function setYear($year)
    {
        $this->year = $year;
        
        return $this;
    }
    
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param College $college
     * @return $this
     */
    public function setCollege(College $college)
    {
        $this->college = $college;
        
        return $this;
    }

    /**
     * @return College
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param Study $study
     * @return $this
     */
    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    public function setPaymentAmount($amount)
    {
        $this->paymentAmount = $amount;

        return $this;
    }

    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;

        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getPaymentMethodForDisplay()
    {
        $method = $this->getPaymentMethod();

        $map = array(
            'creditCard' => 'Credit Card'
        );

        if (!empty($map[$method])) {
            $method = $map[$method];
        } else {
            $method = ucwords($method);
        }

        // System payments show which system
        if ($method == 'System' && $this->getPaymentSystemName()) {
            $method .= ': ' . $this->getPaymentSystemName();
        }

        return $method;
    }

    public function setPaymentSystemName($systemName)
    {
        $this->paymentSystemName = $systemName;

        return $this;
    }

    public function getPaymentSystemName()
    {
        return $this->paymentSystemName;
    }

    public function setObservation(Observation $observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    public function setDigitalSignature($digitalSignature)
    {
        $this->digitalSignature = $digitalSignature;

        return $this;
    }

    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }

    public function setDigitalSignatureTitle($title)
    {
        $this->digitalSignatureTitle = $title;

        return $this;
    }

    public function getDigitalSignatureTitle()
    {
        return $this->digitalSignatureTitle;
    }

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setCompletion($completion)
    {
        $this->completion = $completion;

        return $this;
    }

    public function getCompletion()
    {
        return $this->completion;
    }

    /**
     * @return mixed
     */
    public function getReportAccess()
    {
        return $this->reportAccess;
    }

    /**
     * @param mixed $reportAccess
     * @return Subscription
     */
    public function setReportAccess($reportAccess)
    {
        $this->reportAccess = $reportAccess;
    }

    public function setValue($dbColumn, $value)
    {
        if ($datum = $this->getdatum($dbColumn)) {
            $datum->setValue($value);
            $this->allData[$dbColumn] = $value;
        }
    }

    public function getValue($dbColumn)
    {
        if (is_object($dbColumn) && get_class($dbColumn) == 'Mrss\Entity\Benchmark') {
            $dbColumn = $dbColumn->getDbColumn();
        }

        $value = null;
        if (array_key_exists($dbColumn, $this->allData)) {
            $value = $this->allData[$dbColumn];
        } else {
            if ($datum = $this->getDatum($dbColumn)) {
                $value = $datum->getValue();
                $this->allData[$dbColumn] = $value;
            }
        }

        return $value;
    }

    public function hasValue($benchmark)
    {
        $has = false;
        if ($this->getDatum($benchmark)) {
            $has = true;
        }

        return $has;
    }

    /**
     * Get the data value for one benchmark for this subscription
     * @param mixed $benchmark Can be a dbColumn string or a benchmark object
     * @return Datum
     */
    public function getDatum($benchmark)
    {
        $data = $this->getData();

        if (is_object($benchmark)) {
            $field = 'benchmark';
        } else {
            $field = 'dbColumn';
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq($field, $benchmark));

        $datum = null;
        if ($data) {
            $datums = $data->matching($criteria);
            if ($datums && $datums->count() > 0) {
                $datum = $datums->first();
            }
        }

        // If the data row doesn't exist, create it
        if ($datum === null) {
            $datum = $this->createDatum($benchmark);
        }

        return $datum;
    }

    protected function createDatum($benchmark)
    {
        $datum = new Datum();
        $datum->setSubscription($this);


        if (is_object($benchmark)) {
            $datum->setBenchmark($benchmark);
            $datum->setDbColumn($benchmark->getDbColumn());
        } else {
            $dbColumn = $benchmark;
            $datum->setDbColumn($dbColumn);
            $benchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn);

            $datum->setBenchmark($benchmark);
        }

        if ($benchmark) {
            $this->getData()->add($datum);
            $this->getDatumModel()->save($datum);
            $this->getDatumModel()->getEntityManager()->flush();
        }

        return $datum;
    }

    /**
     * Return an associative array with dbColumn => value.
     * Used to log changes
     *
     */
    public function getAllData()
    {
        $data = array();
        foreach ($this->getData() as $datum) {
            if ($benchmark = $datum->getBenchmark()) {
                $data[$benchmark->getDbColumn()] = $datum->getValue();
            }
        }

        $this->allData = $data;

        return $data;
    }

    public function setValues($data)
    {
        foreach ($data as $dbColumn => $value) {
            $this->setValue($dbColumn, $value);
        }
    }

    /**
     * @return Datum[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Datum[] $data
     * @return Subscription
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param mixed $paid
     * @return $this
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaidNotes()
    {
        return $this->paidNotes;
    }

    /**
     * @param mixed $paidNotes
     * @return $this
     */
    public function setPaidNotes($paidNotes)
    {
        $this->paidNotes = $paidNotes;

        return $this;
    }

    public function addPaidNote($note)
    {
        $separator = "\n";
        $existing = $this->getPaidNotes();

        $new = $existing . $separator . $note;

        $this->setPaidNotes($new);
    }


    /**
     * @return Suppression[]
     */
    public function getSuppressions()
    {
        return $this->suppressions;
    }

    public function getSuppressionList()
    {
        $formUrls = array();
        foreach ($this->getSuppressions() as $suppression) {
            $formUrls[] = $suppression->getBenchmarkGroup()->getUrl();
        }

        $formUrls = array_unique($formUrls);

        return implode(', ', $formUrls);
    }

    public function hasSuppressionFor($benchmarkGroupId)
    {
        $has = false;
        foreach ($this->getSuppressions() as $suppression) {
            if ($suppression->getBenchmarkGroup()->getId() == $benchmarkGroupId) {
                $has = true;
                break;
            }
        }

        return $has;
    }

    public function __toString()
    {
        return "Subscription id: {$this->getId()}";
    }


    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setDatumModel($model)
    {
        $this->datumModel = $model;
    }

    /**
     * @return \Mrss\Model\Datum
     */
    public function getDatumModel()
    {
        return $this->datumModel;
    }

    /**
     * @return Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param Section[] $sections
     * @return Subscription
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    public function getSectionIds()
    {
        $sectionIds = array();
        foreach ($this->getSections() as $section) {
            $sectionIds[] = $section->getId();
        }

        return $sectionIds;
    }

    public function getSectionNames()
    {
        $names = array();
        foreach ($this->getSections() as $section) {
            $names[] = $section->getName();
        }

        return implode(', ', $names);
    }
}
