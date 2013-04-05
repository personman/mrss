<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Benchmark Group metadata
 *
 * This holds info about a groups of benchmarks (aka forms)
 *
 * @ORM\Entity
 * @ORM\Table(name="benchmark_groups")
 */
class BenchmarkGroup
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
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * @ORM\OneToMany(targetEntity="Benchmark", mappedBy="benchmarkGroup")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $benchmarks;

    /**
     * Construct the benchmarkGroup entity
     * Populate the benchmarks property with a placeholder
     */
    public function __construct()
    {
        $this->benchmarks = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function getBenchmarks()
    {
        return $this->benchmarks;
    }
}
