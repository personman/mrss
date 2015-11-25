<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\Benchmark;

/**
 * Entity to track data validation rules to be applied immediately on saving.
 * Any errors are saved as Issues.
 * @todo: study, name, rule (as equation?), message with variables, benchmark(s), severity level,
 *
 * @ORM\Entity
 * @ORM\Table(name="rules")
 */
class Rule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Benchmark
     */
    protected $benchmark;



    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
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
}
