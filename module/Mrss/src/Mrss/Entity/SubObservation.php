<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\Exception;
use Zend\Debug\Debug;

/** @ORM\Entity
 * @ORM\Table(name="sub_observations")
 */
class SubObservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=99, nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Observation", inversedBy="subobservations")
     * * @ORM\JoinColumn(name="observation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $observation;

    /**
     * @ORM\OneToMany(targetEntity="ChangeSet", mappedBy="subObservation", cascade="remove")
     */
    protected $changeSets;

    // MRSS Form 2
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_non_labor_oper_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_fte_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_fte_students_per_fte_faculty;



    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    public function setObservation(Observation $observation)
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * @return Observation
     */
    public function getObservation()
    {
        return $this->observation;
    }

    public function has($benchmark)
    {
        return property_exists($this, $benchmark);
    }


    /**
     * @param $benchmark
     * @return mixed
     * @throws Exception\InvalidBenchmarkException
     */
    public function get($benchmark)
    {
        if (!$this->has($benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        return $this->$benchmark;
    }

    public function set($benchmark, $value)
    {
        if (!property_exists($this, $benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        // Convert empty strings to null so they don't end up as 0
        if ($value === '') {
            $value = null;
        }

        $this->$benchmark = $value;

        return $this;
    }

    public function getArrayCopy()
    {
        $arrayCopy = array();
        foreach ($this as $key => $value) {
            $arrayCopy[$key] = $value;
        }

        return $arrayCopy;
    }

    /**
     * Hydrator method for putting form values into entity
     *
     * @param array $observationArray
     */
    public function populate($observationArray)
    {
        foreach ($observationArray as $key => $value) {
            if ($this->has($key)) {
                $this->set($key, $value);
            }
        }
    }

    public function getAllBenchmarks()
    {
        $benchmarks = array();
        $exclude = array('id', 'observation');
        foreach ($this as $key => $value) {
            if (!in_array($key, $exclude)) {
                $benchmarks[] = $key;
            }
        }

        return $benchmarks;
    }
}
