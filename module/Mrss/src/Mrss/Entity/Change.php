<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\ChangeSet;
use Mrss\Entity\Benchmark;

/**
 * Entity to track Observation changes
 *
 * @ORM\Entity
 * @ORM\Table(name="changes")
 */
class Change
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ChangeSet", inversedBy="changes")
     * @var ChangeSet
     */
    protected $changeSet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oldValue;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $newValue;

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

    public function setChangeSet(ChangeSet $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }

    public function setOldValue($value)
    {
        $this->oldValue = $value;

        return $this;
    }

    public function getOldValue($nullAsString = false)
    {
        $oldValue = $this->oldValue;

        if ($nullAsString && $oldValue === null) {
            $oldValue = 'null';
        }

        return $oldValue;
    }

    public function setNewValue($value)
    {
        $this->newValue = $value;

        return $this;
    }

    public function getNewValue($nullAsString = false)
    {
        $newValue = $this->newValue;

        if ($nullAsString && $newValue === null) {
            $newValue = 'null';
        }

        return $newValue;
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
