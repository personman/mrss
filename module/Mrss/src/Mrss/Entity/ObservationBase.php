<?php

namespace Mrss\Entity;

class ObservationBase
{



    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    /**
     * @return \Mrss\Entity\College
     */
    public function getCollege()
    {
        return $this->college;
    }

    public function setCipCode($cipCode)
    {
        $this->cipCode = $cipCode;

        return $this;
    }

    public function getCipCode()
    {
        return $this->cipCode;
    }

    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
        return $this;
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function setSubObservations($subObservations)
    {
        $this->subObservations = $subObservations;

        return $this;
    }

    public function getSubObservations()
    {
        return $this->subObservations;
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

        // Convert arrays to string
        if (is_array($value)) {
            $value = implode("\n", $value);
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
        $exclude = array('id', 'year', 'cipCode', 'college', 'subObservations');
        foreach ($this as $key => $value) {
            if (!in_array($key, $exclude)) {
                $benchmarks[] = $key;
            }
        }

        return $benchmarks;
    }

    /**
     * This is really only for MRSS
     */
    public function mergeSubobservations()
    {
        $this->sumSubobservations();

        $prefix = 'inst_cost_';
        $facultyTypes = array('full', 'part');
        $activities = array(
            'program_dev',
            'course_dev',
            'teaching',
            'tutoring',
            'advising',
            'ac_service',
            'assessment',
            'prof_dev'
        );

        foreach ($activities as $activity) {
            foreach ($facultyTypes as $facultyType) {
                // Build some property names
                $percentageField = $prefix . $facultyType . '_' . $activity;
                $activityCostField = $prefix . $facultyType . '_expend_' . $activity;
                $costField = $prefix . $facultyType . '_expend';
                //$perCreditHourField = $prefix . $facultyType .
                //    '_cred_hr_' . $activity;

                $totalCost = 0;
                $activityCost = 0;
                $activityPercentage = 0;

                //$percentagesOfTimeSpentOnActivity = array();

                // Get the total cost
                foreach ($this->getSubObservations() as $subobservation) {
                    $acCost = $subobservation->get($costField);
                    $totalCost += $acCost;
                }

                // Loop over the subobservations
                foreach ($this->getSubObservations() as $subobservation) {
                    $percentageSpentOn = $subobservation->get($percentageField);
                    $acCost = $subobservation->get($costField);

                    //$percentagesOfTimeSpentOnActivity[] = $percentageSpentOn;

                    // If we've got null values, skip it
                    if (!is_null($percentageSpentOn) || !is_null($acCost)) {
                        $cost = ($percentageSpentOn / 100) * $acCost;
                        $activityCost += $cost;
                    }
                }

                // Activity percentage
                if ($totalCost) {
                    $activityPercentage  = $activityCost / $totalCost * 100;
                } else {
                    $activityPercentage = 0;
                }

                // Now save the cost
                $this->set($activityCostField, $activityCost);
                $this->set($percentageField, $activityPercentage);

                // Average the percentages of time spent on the activity
                /*if (count($percentagesOfTimeSpentOnActivity)) {
                    $average = array_sum($percentagesOfTimeSpentOnActivity) /
                        count($percentagesOfTimeSpentOnActivity);
                } else {
                    $average = 0;
                }

                $this->set($percentageField, $average);*/
            }
        }
    }


    public function sumSubobservations()
    {
        $values = array(
            'inst_cost_full_cred_hr' => 0,
            'inst_cost_part_cred_hr' => 0,
            'inst_cost_full_expend' => 0,
            'inst_cost_part_expend' => 0,
            'inst_cost_full_num' => 0,
            'inst_cost_part_num' => 0
        );

        foreach ($this->getSubObservations() as $subObservation) {
            foreach ($values as $key => $value) {
                $values[$key] += $subObservation->get($key);
            }
        }

        // Save them
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function __toString()
    {
        return "Observation id: {$this->getId()}";
    }

    public function getAllProperties()
    {
        return get_object_vars($this);
    }
}
