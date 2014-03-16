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
     */
    protected $benchmark;

    /**
     * @ORM\Column(type="integer")
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

}
