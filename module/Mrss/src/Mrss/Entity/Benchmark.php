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
 * @ORM\Table(name="benchmarks")
 */
class Benchmark implements FormElementProviderInterface, InputFilterAwareInterface
{
    protected $inputFilter;

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
     * @ORM\Column(type="string")
     */
    protected $dbColumn;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $inputType;

    /**
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="BenchmarkGroup", inversedBy="benchmarks")
     */
    protected $benchmarkGroup;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $yearsAvailable;

    protected $benchmarkModel;
    protected $completionPercentages;

    /**
     * Construct the benchmark entity
     * Populate the years property with a placeholder
     */
    public function __construct()
    {
        $this->years = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;

        return $this;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getBenchmarkGroup()
    {
        return $this->benchmarkGroup;
    }

    public function setYearsAvailable($years)
    {
        $this->yearsAvailable = implode(',', $years);

        return $this;
    }

    public function getYearsAvailable()
    {
        return array_map('intval', explode(',', $this->yearsAvailable));
    }

    public function isAvailableForYear($year)
    {
        $available = $this->getYearsAvailable();

        return in_array($year, $available);
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;
    }

    /**
     * Implement the FormElementProviderInterface
     *
     * @todo: different number types (int, percentage, float, etc)
     */
    public function getFormElement()
    {
        return array(
            'name' => $this->getDbColumn(),
            'options' => array(
                'label' => $this->getName(),
                'help-block' => $this->getDescription()
            ),
            'attributes' => array(
                'class' => 'input-small'
            )
        );
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'name',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim')
                        ),
                        'validators' => array(
                            array('name' => 'NotEmpty')
                        )
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getCompletionPercentages()
    {
        if (empty($this->completionPercentages)) {
            $this->completionPercentages = $this->benchmarkModel
                ->getCompletionPercentages(
                    $this->getDbColumn(),
                    $this->getYearsAvailable()
                );
        }

        return $this->completionPercentages;
    }

    public function getCompletionPercentage($year)
    {
        $years = $this->getCompletionPercentages();

        if (!empty($years[$year])) {
            $percentage = $years[$year];
        } else {
            $percentage = null;
        }

        return $percentage;
    }
}
