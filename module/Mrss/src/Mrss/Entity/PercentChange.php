<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity to track data issues
 *
 * @ORM\Entity
 * @ORM\Table(name="percent_changes")
 */
class PercentChange
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="benchmarkGroups")
     */
    protected $study;


    /**
     * Should year and college be combined to the subscription id?
     * Maybe not as that would make it harder to search by year or by college.
     * @ORM\Column(type="integer")
     */
    protected $year;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="observations")
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark",)
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Benchmark
     */
    protected $benchmark;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oldValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $percentChange;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $staffNote;

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue($nullAsString = false)
    {
        $value = $this->value;

        if ($nullAsString && $value === null) {
            $value = 'null';
        }

        return $value;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    /**
     * @return \Mrss\Entity\College
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @return mixed
     */
    public function getStaffNote()
    {
        return $this->staffNote;
    }

    /**
     * @param mixed $staffNote
     * @return Issue
     */
    public function setStaffNote($staffNote)
    {
        $this->staffNote = $staffNote;

        return $this;
    }

    /**
     * @return Benchmark
     */
    public function getBenchmark()
    {
        return $this->benchmark;
    }

    /**
     * @param Benchmark $benchmark
     * @return PercentChnage
     */
    public function setBenchmark($benchmark)
    {
        $this->benchmark = $benchmark;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * @param mixed $oldValue
     * @return PercentChnage
     */
    public function setOldValue($oldValue)
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPercentChange()
    {
        return $this->percentChange;
    }

    /**
     * @param mixed $percentChange
     * @return PercentChnage
     */
    public function setPercentChange($percentChange)
    {
        $this->percentChange = $percentChange;
        return $this;
    }
}
