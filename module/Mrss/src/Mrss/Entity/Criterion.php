<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="criteria")
 */
class Criterion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="criteria")
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="Benchmark")
     */
    protected $benchmark;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $helpText;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param mixed $study
     * @return $this
     */
    public function setStudy(Study $study)
    {
        $this->study = $study;

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
     * @param mixed $benchmark
     * @return $this
     */
    public function setBenchmark(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param mixed $sequence
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * @param mixed $helpText
     * @return $this
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }
}
