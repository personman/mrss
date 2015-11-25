<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity to track data issues
 *
 * @ORM\Entity
 * @ORM\Table(name="issues")
 */
class Issue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ChangeSet", inversedBy="changes", nullable=true)
     * @var ChangeSet
     */
    protected $changeSet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Benchmark
     */
    protected $benchmark;

    // @todo: entity to represent the rule that was broken
    // @todo: study and year and status and probably staff note and user note

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

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue($nullAsString = false)
    {
        $value = $this->value;

        if ($nullAsString && $value === null) {
            $value = 'null';
        }

        return $value;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
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
