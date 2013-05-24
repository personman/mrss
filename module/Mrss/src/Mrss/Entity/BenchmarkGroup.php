<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * Benchmark Group metadata
 *
 * This holds info about a groups of benchmarks (aka forms)
 *
 * @ORM\Entity
 * @ORM\Table(name="benchmark_groups")
 */
class BenchmarkGroup implements FormFieldsetProviderInterface,
 InputFilterAwareInterface
{
    protected $inputFilter;

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
     * Imported key used to match up with benchmarks
     *
     * @ORM\Column(type="string")
     */
    protected $shortName;

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
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="benchmarkGroups")
     */
    protected $study;

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

    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getShortName()
    {
        return $this->shortName;
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

    public function setBenchmarks($benchmarks)
    {
        $this->benchmarks = $benchmarks;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    /**
     * Implement the FormFieldSetProvider interface so this can be turned
     * into a fieldset.
     *
     * @param $year
     * @return array
     */
    public function getElements($year)
    {
        return $this->getNonComputedBenchmarksForYear($year);
    }

    public function getLabel()
    {
        return $this->getName();
    }

    /**
     * Leave out computed benchmarks, we don't need to show them in the form
     *
     * @param $year
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getNonComputedBenchmarksForYear($year)
    {
        $benchmarks = $this->getBenchmarksForYear($year);
        $nonComputedBenchmarks = array();

        foreach ($benchmarks as $benchmark) {
            if ($benchmark->getInputType() != 'computed') {
                $nonComputedBenchmarks[] = $benchmark;
            }
        }

        return $nonComputedBenchmarks;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param $year
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getBenchmarksForYear($year)
    {
        $benchmarksForYear = array();

        $benchmarks = $this->getBenchmarks();
        foreach ($benchmarks as $benchmark) {
            if ($benchmark->isAvailableForYear($year)) {
                $benchmarksForYear[] = $benchmark;
            }
        }

        return $benchmarksForYear;
    }

    /**
     * Percentage of completed benchmarks for this group in the given observation
     *
     * @param Observation $observation
     * @return float
     */
    public function getCompletionPercentageForObservation(Observation $observation)
    {
        $total = count($this->getBenchmarksForYear($observation->getYear()));
        $completed = $this->countCompleteFieldsInObservation($observation);

        if ($total > 0) {
            $percentage = round($completed / $total * 100, 3);
        } else {
            $percentage = 0.0;
        }

        return $percentage;
    }

    /**
     * Return the number of non-null fields in this group for the observation
     *
     * @param Observation $observation
     * @return int
     */
    public function countCompleteFieldsInObservation(Observation $observation)
    {
        $complete = 0;

        foreach ($this->getBenchmarksForYear($observation->getYear()) as $benchmark) {
            $value = $observation->get($benchmark->getDbColumn());

            if ($value != null) {
                $complete++;
            }
        }

        return $complete;
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

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'shortName',
                        'required' => true,
                        'filters' => array(
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
}
