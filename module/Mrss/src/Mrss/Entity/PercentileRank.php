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
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     */
    protected $benchmark;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    /**
     * @ORM\Column(type="float")
     */
    protected $rank;


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

    public function getBenchmark()
    {
        return $this->benchmark;
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
}
