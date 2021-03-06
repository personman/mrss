<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chart - @todo: Delete this file and remove the db table
 *
 * Configuration for a chart
 *
 * @ORM\Entity
 * @deprecated
 * @ORM\Table(name="charts")
 */
class Chart
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(
     * name="college_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $college;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(
     * name="highlighted_college_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $highlightedCollege;

    /**
     * A JSON array
     * @ORM\Column(type="text", nullable=true)
     */
    protected $config;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark1_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark1;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark2_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark2;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark3_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $benchmark3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $year;

    /**
     * @param mixed $benchmark1
     */
    public function setBenchmark1($benchmark1)
    {
        $this->benchmark1 = $benchmark1;
    }

    /**
     * @return mixed
     */
    public function getBenchmark1()
    {
        return $this->benchmark1;
    }

    /**
     * @param mixed $benchmark2
     */
    public function setBenchmark2($benchmark2)
    {
        $this->benchmark2 = $benchmark2;
    }

    /**
     * @return mixed
     */
    public function getBenchmark2()
    {
        return $this->benchmark2;
    }

    /**
     * @param mixed $benchmark3
     */
    public function setBenchmark3($benchmark3)
    {
        $this->benchmark3 = $benchmark3;
    }

    /**
     * @return mixed
     */
    public function getBenchmark3()
    {
        return $this->benchmark3;
    }

    /**
     * @param mixed $college
     */
    public function setCollege($college)
    {
        $this->college = $college;
    }

    /**
     * @return mixed
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $config = json_encode($config);
        }

        $this->config = $config;
    }

    /**
     * @param bool $decodeJson
     * @return mixed
     */
    public function getConfig($decodeJson = true)
    {
        $config = $this->config;

        if ($decodeJson) {
            $config = json_decode($config, true);
        }

        return $config;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $highlightedCollege
     */
    public function setHighlightedCollege($highlightedCollege)
    {
        $this->highlightedCollege = $highlightedCollege;
    }

    /**
     * @return mixed
     */
    public function getHighlightedCollege()
    {
        return $this->highlightedCollege;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $study
     */
    public function setStudy($study)
    {
        $this->study = $study;
    }

    /**
     * @return mixed
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }
}
