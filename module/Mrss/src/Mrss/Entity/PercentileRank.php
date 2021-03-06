<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="percentile_ranks")
 */
class PercentileRank
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
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(name="college_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * This causes the percentilRank row to be deleted when the benchmark is deleted
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
     * @ORM\Column(type="float")
     */
    protected $rank;

    protected $highIsBetter = true;


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

    public function setCollege($college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function setBenchmark(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    /**
     * @return Benchmark
     */
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

    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    public function getRank()
    {
        return $this->rank;
    }

    public function setHighIsBetter($is)
    {
        $this->highIsBetter = $is;

        return $this;
    }

    public function getHighIsBetter()
    {
        return $this->highIsBetter;
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
