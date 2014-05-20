<?php

namespace Mrss\Entity;

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
     * @ORM\ManyToOne(targetEntity="Study")
     * @var Study
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @var College
     */
    protected $college;

    /**
     * @ORM\Column(type="integer")
     * @var integer $year
     */
    protected $year;

    /**
     * @ORM\Column(type="string", nullable=true)
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

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
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

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
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
