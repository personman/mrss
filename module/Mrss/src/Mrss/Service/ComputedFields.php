<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;
use Mrss\Entity\BenchmarkGroup;
use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use Mrss\Entity\Study;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Model\Observation as ObservationkModel;
use Mrss\Model\SubObservation as SubObservationkModel;
use exprlib\Parser as MathParser;

class ComputedFields
{
    /**
     * @var BenchmarkModel
     */
    protected $benchmarkModel;

    /**
     * @var ObservationkModel
     */
    protected $observationModel;

    /**
     * @var SubObservationkModel
     */
    protected $subObservationModel;

    /**
     * @var Benchmark[]
     */
    protected $computedBenchmarks;

    /**
     * @var Study
     */
    protected $study;

    protected $debug = false;

    protected $error;

    public function calculate(
        Benchmark $benchmark,
        Observation $observation,
        $flush = true,
        SubObservation $subObservation = null
    ) {

        if ($this->debug) {
            $start = microtime(1);
        }

        $equationWithVariables = $benchmark->getEquation();
        $benchmarkColumn = $benchmark->getDbColumn();
        if ($this->debug) {
            echo "equation prepared: " . round(microtime(1) - $start, 3) . "s<br>";
        }

        if (empty($equationWithVariables)) {
            $result = null;
        } else {
            // Populate variables
            $equationWithVariables = $this
                ->nestComputedEquations(
                    $equationWithVariables,
                    $observation->getYear()
                );

            $equation = $this->prepareEquation($equationWithVariables, $observation, $subObservation);

            if (empty($equation)) {
                $result = null;
            } else {
                // the Calculation
                try {
                    $result = $equation->evaluate();
                } catch (\Exception $exception) {
                    // An exception was thrown, possibly division by zero
                    // Save the value as null
                    $result = null;
                }
            }
        }

        // If the result is meant to be a percentage, multiply by 100
        if (!is_null($result) && $benchmark->isPercent()) {
            $result = $result * 100;
        }

        if ($this->debug) {
            echo "Result = $result. About to flush (if applicable): " . round(microtime(1) - $start, 3) . "s<br>";
        }

        // Save the computed value
        if (!empty($subObservation)) {
            $subObservation->set($benchmarkColumn, $result);
            $this->getSubObservationModel()->save($subObservation);
        } else {
            $observation->set($benchmarkColumn, $result);
            $this->getObservationModel()->save($observation);
        }

        if ($flush) {
            $this->getObservationModel()->getEntityManager()->flush();
            if ($this->debug) {
                echo "flushed: " . round(microtime(1) - $start, 3) . "s<br>";
            }
        }
        return $result;
    }

    public function checkEquation($equation)
    {
        $variables = $this->getVariables($equation);

        $parsedEquation = $this->buildEquation($equation);
        $observation = new Observation();

        $result = true;
        $vars = array();
        foreach ($variables as $variable) {
            if (!$observation->has($variable)) {
                $result = false;
                $error = "$variable does not exist in the observation";
            } else {
                $vars[$variable] = rand(1, 99);
            }
        }

        $preparedEquation = $parsedEquation->setVars($vars);

        try {
            $equationResult = $preparedEquation->evaluate();
        } catch (\Exception $exception) {
            // An exception was thrown, possibly division by zero
            // Save the value as null
            $result = false;

            $message = null;
            if (method_exists($exception, 'getMessage')) {
                $message = $exception->getMessage();

                if (empty($message)) {
                    $message = get_class($exception);
                }
            }
            $error = 'Equation parse error. ' . $message;
        }



        if (!empty($error)) {
            $this->error = $error;
        } else {
            $this->error = null;
        }

        return $result;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getVariables($equation)
    {
        // Extract the variables
        preg_match_all('/{{(.*?)}}/', $equation, $matches);

        $variables = $matches[1];

        return $variables;
    }

    /**
     * Accept a string equation and return the matchparser
     *
     * @param string $equation
     * @return MathParser
     */
    public function buildEquation($equation)
    {
        return MathParser::build($equation);
    }

    /**
     * @param $equation
     * @param Observation $observation
     * @param \Mrss\Entity\SubObservation $subObservation
     * @throws \Exception
     * @return MathParser
     */
    public function prepareEquation($equation, Observation $observation, SubObservation $subObservation = null)
    {
        $variables = $this->getVariables($equation);

        $parsedEquation = $this->buildEquation($equation);

        $errors = array();

        $vars = array();
        foreach ($variables as $variable) {
            if (!empty($subObservation)) {
                $value = $subObservation->get($variable);
            } else {
                $value = $observation->get($variable);
            }

            // If any of the variables are null or '', bail out
            if ($value === null || $value === '') {
                $errors[] = "Missing variable: $variable. ";

                continue;
            }

            $vars[$variable] = $value;
        }

        if (!empty($errors) && !$this->debug) {
            return false;
        }


        if ($this->debug) {
            pr($observation->getId());
            pr($variables);
            pr($errors);
            pr($vars);
            echo 'Observation id:';
            pr($observation->getId());
        }

        if (!empty($errors)) {
            $preparedEquation = '';
        } else {
            $preparedEquation = $parsedEquation->setVars($vars);
        }


        if (empty($preparedEquation)) {
            if ($this->debug) {
                throw new \Exception(
                    'Invalid equation: ' .
                    "<br>" .
                    $equation . "<br>" .
                    "Variables: <br >" . print_r($vars, 1) .
                    print_r($errors, 1)
                );
            }

            return false;
        }


        return $preparedEquation;
    }

    /**
     * If an equation includes a benchmark that's computed, drop in the equation
     * rather than the current value. This is so we don't have to worry about
     * what order the equations are calculated in.
     *
     * @param $equation
     * @param $year
     * @return mixed
     */
    public function nestComputedEquations($equation, $year)
    {
        $variables = $this->getVariables($equation);
        $computed = $this->getComputedBenchmarks($year);

        foreach ($variables as $variable) {
            if (!empty($computed[$variable])) {
                $insideBenchmark = $computed[$variable];
                $insideEquation = $insideBenchmark->getEquation();

                // Multiply by 100 for percentages
                if ($insideBenchmark->isPercent()) {
                    $insideEquation = "($insideEquation * 100)";
                }

                // Recurse in case the inside equation contains other computed ones
                $insideEquation = $this->nestComputedEquations($insideEquation, $year);

                $equation = str_replace(
                    '{{' . $variable . '}}',
                    '( ' . $insideEquation . ' )',
                    $equation
                );
            }
        }

        return $equation;
    }

    public function calculateAllForObservation(Observation $observation)
    {
        if (empty($observation)) {
            die('empty');
        }
        $benchmarks = $this->getComputedBenchmarks($observation->getYear());

        foreach ($benchmarks as $benchmark) {
            if ($this->debug) {
                pr($benchmark->getName());
            }

            try {
                $this->calculate($benchmark, $observation, false);
            } catch (\Exception $e) {
                //pr($e->getMessage());
                //prd($e);
            }
        }

        $this->getObservationModel()->getEntityManager()->flush();
    }

    public function calculateAllForSubObservation(SubObservation $subObservation, BenchmarkGroup $benchmarkGroup)
    {
        $observation = $subObservation->getObservation();
        $year = $observation->getYear();
        $benchmarks = $benchmarkGroup->getComputedBenchmarksForYear($year);

        foreach ($benchmarks as $benchmark) {
            try {
                $this->calculate($benchmark, $observation, false, $subObservation);
            } catch (\Exception $e) {
                pr($e->getMessage());
                echo 'Benchmark: ' . $benchmark->getDbColumn() . ', equation: ' . $benchmark->getEquation();
            }
        }
    }

    public function setBenchmarkModel(BenchmarkModel $benchmarkModel)
    {
        $this->benchmarkModel = $benchmarkModel;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setObservationModel(ObservationkModel $model)
    {
        $this->observationModel = $model;

        return $this;
    }

    public function getObservationModel()
    {
        return $this->observationModel;
    }

    public function setSubObservationModel(SubObservationkModel $model)
    {
        $this->subObservationModel = $model;

        return $this;
    }

    public function getSubObservationModel()
    {
        return $this->subObservationModel;
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

    public function getComputedBenchmarks($year)
    {
        if (empty($this->computedBenchmarks)) {
            $benchmarks = $this->getBenchmarkModel()->findComputed($this->getStudy());

            $computedBenchmarks = array();
            foreach ($benchmarks as $benchmark) {
                $computeAfter = $benchmark->getComputeAfter();
                if (!empty($computeAfter) && $year <= $computeAfter) {
                    // Skip computed benchmarks that are static in prior years
                    continue;
                }

                $computedBenchmarks[$benchmark->getDbColumn()] = $benchmark;
            }

            $this->computedBenchmarks = $computedBenchmarks;
        }

        return $this->computedBenchmarks;
    }
}
