<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for a single data point
 *
 * @ORM\Entity
 * @ORM\Table(name="data_values")
 */
class Datum
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Subscription", inversedBy="data")
     * @var Subscription
     */
    protected $subscription;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark",)
     * @ORM\JoinColumn(name="benchmark_id", referencedColumnName="id", onDelete="CASCADE")
     * @var Benchmark
     */
    protected $benchmark;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $floatValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $stringValue;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Datum
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param Subscription $subscription
     * @return Datum
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }

    /**
     * @return Benchmark
     */
    public function getBenchmark()
    {
        return $this->benchmark;
    }

    /**
     * @param Benchmark $benchmark
     * @return Datum
     */
    public function setBenchmark($benchmark)
    {
        $this->benchmark = $benchmark;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFloatValue()
    {
        return $this->floatValue;
    }

    /**
     * @param mixed $floatValue
     * @return Datum
     */
    public function setFloatValue($floatValue)
    {
        $this->floatValue = $floatValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param mixed $stringValue
     * @return Datum
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
        return $this;
    }
}
