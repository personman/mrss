<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Benchmark metadata
 *
 * This holds info about a benchmark, like label and description,
 * but the actual data is in the observations table/entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="benchmark_headings")
 */
class BenchmarkHeading
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
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dbColumn;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * Type should be 'data-entry' or 'report'
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="BenchmarkGroup", inversedBy="benchmarkHeadings")
     * @ORM\JoinColumn(name="benchmarkGroup_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $benchmarkGroup;

    /**
     * @param mixed $benchmarkGroup
     */
    public function setBenchmarkGroup($benchmarkGroup)
    {
        $this->benchmarkGroup = $benchmarkGroup;
    }

    /**
     * @return mixed
     */
    public function getBenchmarkGroup()
    {
        return $this->benchmarkGroup;
    }

    /**
     * @param mixed $dbColumn
     */
    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
    }

    /**
     * @return mixed
     */
    public function getDbColumn()
    {
        return $this->dbColumn;
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
     * @param mixed $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return mixed
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
}
