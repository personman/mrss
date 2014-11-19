<?php

namespace Mrss\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $format;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $useSubObservation;

    /**
     * @param mixed $benchmarkHeadings
     */
    public function setBenchmarkHeadings($benchmarkHeadings)
    {
        $this->benchmarkHeadings = $benchmarkHeadings;
    }

    /**
     * @return BenchmarkHeading[]
     */
    public function getBenchmarkHeadings()
    {
        return $this->benchmarkHeadings;
    }

    public function getBenchmarkHeadingByName($name)
    {
        $match = null;
        foreach ($this->getBenchmarkHeadings() as $heading) {
            if ($heading->getName() == $name) {
                $match = $heading;
                break;
            }
        }

        return $match;
    }

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
     * @ORM\OneToMany(targetEntity="BenchmarkHeading", mappedBy="benchmarkGroup", cascade={"persist", "remove"})
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $benchmarkHeadings;

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
        $this->benchmarks = new ArrayCollection();
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

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat()
    {
        return $this->format;
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

    public function setUseSubObservation($use)
    {
        $this->useSubObservation = $use;

        return $this;
    }

    public function getUseSubObservation()
    {
        return $this->useSubObservation;
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

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
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
            if (!$benchmark->getComputed()) {
                $nonComputedBenchmarks[] = $benchmark;
            }
        }

        return $nonComputedBenchmarks;
    }

    /**
     * Leave out noncomputed benchmarks, we don't need to show them in the form
     *
     * @param $year
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getComputedBenchmarksForYear($year)
    {
        $benchmarks = $this->getBenchmarksForYear($year);
        $computedBenchmarks = array();

        foreach ($benchmarks as $benchmark) {
            if ($benchmark->getComputed()) {
                $computedBenchmarks[] = $benchmark;
            }
        }

        return $computedBenchmarks;
    }

    /**
     * Leave out computed benchmarks and those those excluded from completion calc
     *
     * @param $year
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getBenchmarksForCompletionCalculationForYear($year)
    {
        $benchmarks = $this->getBenchmarksForYear($year);
        $benchmarksForCompletion = array();

        foreach ($benchmarks as $benchmark) {
            if (!$benchmark->getComputed()
                && !$benchmark->getExcludeFromCompletion()) {
                $benchmarksForCompletion[] = $benchmark;
            }
        }

        //if ($year == 2010 && $this->getId() == 4) {
            //pr(count($benchmarks));
            //prd(count($benchmarksForCompletion));
        //}

        return $benchmarksForCompletion;
    }

    /**
     * Number of benchmarks, for calculation completion.
     * Handles subobservations, too.
     *
     * @param Observation $observation
     * @return int
     */
    public function getBenchmarkCount(Observation $observation)
    {
        if ($this->getUseSubObservation()) {
            return $this->getSubObservationBenchmarkCount(
                $observation
            );
        }

        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        return count($benchmarks);
    }

    public function getSubObservationBenchmarkCount(Observation $observation)
    {
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        // The subobservations
        $subobservations = $observation->getSubObservations();

        // Number of fields
        $count = count($benchmarks) * count($subobservations);

        return $count;
    }

    /**
     * @param $year
     * @return \Mrss\Entity\Benchmark[]
     */
    public function getBenchmarksForYear($year = null)
    {
        $benchmarksForYear = array();

        $benchmarks = $this->getBenchmarks();
        //if ($year == 2010) pr(count($benchmarks));
        foreach ($benchmarks as $benchmark) {
            if (is_null($year) || $benchmark->isAvailableForYear($year)) {
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
        $total = $this->getBenchmarkCount($observation);
        $completed = $this->countCompleteFieldsInObservation($observation);

        if ($total > 0) {
            $percentage = round($completed / $total * 100, 3);
        } else {
            $percentage = 0.0;
        }

        return $percentage;
    }

    public function getSubObservationCompletionPercentageForObservation(
        Observation $observation
    ) {
        // The benchmarks
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        // The subobservations
        $subobservations = $observation->getSubObservations();

        // Number of fields
        $count = count($benchmarks) * count($subobservations);

        // Completed fields
        $completed = 0;
        foreach ($benchmarks as $benchmark) {
            foreach ($subobservations as $subobservation) {
                $value = $subobservation->get($benchmark->getDbColumn());

                if ($value !== null) {
                    $completed++;
                }
            }
        }

        if (empty($count)) {
            $percentage = 0;
        } else {
            $percentage = round($completed / $count * 100, 3);
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
        if ($this->getUseSubObservation()) {
            return $this->countCompleteFieldsInSubobservations(
                $observation
            );
        }

        $complete = 0;
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        foreach ($benchmarks as $benchmark) {
            $value = $observation->get($benchmark->getDbColumn());

            if ($value !== null) {
                $complete++;
            }
        }

        return $complete;
    }

    public function countCompleteFieldsInSubobservations(Observation $observation)
    {
        // The benchmarks
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        // The subobservations
        $subobservations = $observation->getSubObservations();

        // Completed fields
        $completed = 0;
        foreach ($benchmarks as $benchmark) {
            foreach ($subobservations as $subobservation) {
                $value = $subobservation->get($benchmark->getDbColumn());

                if ($value !== null) {
                    $completed++;
                }
            }
        }

        return $completed;
    }

    public function getIncompleteBenchmarksForObservation(Observation $observation)
    {
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        $incompletes = array();
        foreach ($benchmarks as $benchmark) {
            $value = $observation->get($benchmark->getDbColumn());

            if ($value === null) {
                $incompletes[] = $benchmark;
            }
        }

        return $incompletes;
    }

    public function getCompleteBenchmarksForObservation(Observation $observation)
    {
        $benchmarks = $this->getBenchmarksForCompletionCalculationForYear(
            $observation->getYear()
        );

        $completes = array();
        foreach ($benchmarks as $benchmark) {
            $value = $observation->get($benchmark->getDbColumn());

            if ($value !== null) {
                $completes[] = $benchmark;
            }
        }

        return $completes;
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

    public function getChildren($year = null, $includeComputed = true)
    {
        $children = array();

        // Include computed?
        if ($includeComputed) {
            $benchmarks = $this->getBenchmarksForYear($year);
        } else {
            $benchmarks = $this->getNonComputedBenchmarksForYear($year);
        }

        foreach ($benchmarks as $benchmark) {
            $children[$benchmark->getSequence()] = $benchmark;
        }
        foreach ($this->getBenchmarkHeadings() as $heading) {
            $children[$heading->getSequence()] = $heading;
        }

        ksort($children, SORT_NUMERIC);

        return $children;
    }
}
