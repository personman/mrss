<?php

namespace Mrss\Entity;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="outliers")
 */
class Outlier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark",)
     * @var Benchmark
     */
    protected $benchmark;

    /**
     * @ORM\ManyToOne(targetEntity="Observation",)
     * @var Observation
     */
    protected $observation;

    /**
     * @ORM\Column(type="string", nullabe=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="string")
     */
    protected $problem;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setBenchmark($benchmark)
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    public function getBenchmark()
    {
        return $this->benchmark;
    }

    public function setObservation(Observation $observation)
    {
        $this->observation = $observation;

        return $this;
    }

    public function getObservation()
    {
        return $this->observation;
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

    public function setProblem($problem)
    {
        $this->problem = $problem;

        return $this;
    }

    public function getProblem()
    {
        return $this->problem;
    }
}
