<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="percentiles")
 */
class Percentile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $year;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $cipCode;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $benchmark;

    /**
     * @ORM\ManyToOne(targetEntity="System")
     * @ORM\JoinColumn(
     *      name="system_id",
     *      referencedColumnName="id",
     *      onDelete="CASCADE",
     *      nullable=true
     * )
     */
    protected $system;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $forPercentChange = false;

    /**
     * @ORM\Column(type="string")
     */
    protected $percentile;

    /**
     * @ORM\Column(type="float")
     */
    protected $value;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    public function setCipCode($cipCode)
    {
        $this->cipCode = $cipCode;

        return $this;
    }

    public function getCipCode()
    {
        return $this->cipCode;
    }

    public function setBenchmark(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    public function getBenchmark()
    {
        return $this->benchmark;
    }

    public function setSystem(System $system)
    {
        $this->system = $system;

        return $this;
    }

    public function getSystem()
    {
        return $this->system;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setPercentile($percentile)
    {
        $this->percentile = $percentile;

        return $this;
    }

    public function getPercentile()
    {
        return $this->percentile;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getForPercentChange()
    {
        return $this->forPercentChange;
    }

    /**
     * @param mixed $forPercentChange
     * @return PercentileRank
     */
    public function setForPercentChange($forPercentChange)
    {
        $this->forPercentChange = $forPercentChange;
        return $this;
    }
}
