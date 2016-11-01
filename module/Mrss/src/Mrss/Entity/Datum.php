<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for a single data point
 *
 * @ORM\Entity
 * @ORM\Table(name="data_values",indexes={@ORM\Index(name="dbColumnIndex", columns={"dbColumn"})})
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
     * @ORM\ManyToOne(targetEntity="Benchmark", fetch="EAGER")
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $dbColumn;

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
        // Also set the string from here
        $this->setStringValue($floatValue);

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

    public function setValue($value)
    {
        if ($this->usesString()) {
            $this->setStringValue($value);
        } else {
            if (is_null($value) || $value === '') {
                $value = null;
            }

            if ($value) {
                $value = floatval($value);
            }

            $this->setFloatValue($value);
        }
    }

    public function getValue()
    {
        $value = null;

        if ($this->usesString()) {
            $value = $this->getStringValue();
        } else {
            $value = $this->getFloatValue();
        }

        return $value;
    }

    protected function usesString()
    {
        $usesString = false;

        if ($benchmark = $this->getBenchmark()) {
            $stringInputTypes = array('radio', 'text', 'textarea');
            if (in_array($benchmark->getInputType(), $stringInputTypes)) {
                $usesString = true;
            }
        }

        return $usesString;
    }

    /**
     * @return mixed
     */
    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    /**
     * @param mixed $dbColumn
     * @return Datum
     */
    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
        return $this;
    }
}
