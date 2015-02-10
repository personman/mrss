<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Benchmarks chosen during peer reports
 *
 * @ORM\Entity
 * @ORM\Table(name="peer_benchmarks")
 */
class PeerBenchmark
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * This causes the peerBenchmark row to be deleted when the benchmark is deleted
     */
    protected $benchmark;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(name="college_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="benchmarkGroups")
     */
    protected $study;


    /**
     * @param mixed $benchmark
     */
    public function setBenchmark($benchmark)
    {
        $this->benchmark = $benchmark;
    }

    /**
     * @return mixed
     */
    public function getBenchmark()
    {
        return $this->benchmark;
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
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
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
}
