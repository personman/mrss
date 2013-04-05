<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Benchmark metadata
 *
 * This holds info about a benchmark, like label and description,
 * but the actual data is in the observations table/entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="benchmarks")
 */
class Benchmark
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
     * @ORM\Column(type="string")
     */
    protected $dbColumn;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * @ORM\ManyToOne(targetEntity="BenchmarkGroup", inversedBy="benchmarks")
     */
    protected $benchmarkGroup;

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

    public function setDbColumn($column)
    {
        $this->dbColumn = $column;

        return $this;
    }

    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function setBenchmarkGroup(BenchmarkGroup $benchmarkGroup)
    {
        $this->benchmarkGroup = $benchmarkGroup;

        return $this;
    }

    public function getBenchmarkGroup()
    {
        return $this->benchmarkGroup;
    }

}
