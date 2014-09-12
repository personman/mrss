<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Mrss\Entity\Study;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Model\Observation as ObservationkModel;
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
     * @var Benchmark[]
     */
    protected $computedBenchmarks;

    /**
     * @var Study
     */
    protected $study;

    protected $debug = false;

    public function calculate(Benchmark $benchmark, Observation $observation)
    {
        $equationWithVariables = $benchmark->getEquation();
        $benchmarkColumn = $benchmark->getDbColumn();

        if (empty($equationWithVariables)) {
            $result = null;
        } else {
            // Populate variables
            $equationWithVariables = $this
                ->nestComputedEquations($equationWithVariables);

            $equation = $this->prepareEquation($equationWithVariables, $observation);

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

        // Save the computed value
        $observation->set($benchmarkColumn, $result);
        $this->getObservationModel()->save($observation);
        $this->getObservationModel()->getEntityManager()->flush();

        return $result;
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
     * @return MathParser
     * @throws \Exception
     */
    public function prepareEquation($equation, Observation $observation)
    {
        $variables = $this->getVariables($equation);

        $parsedEquation = $this->buildEquation($equation);

        $errors = array();

        $vars = array();
        foreach ($variables as $variable) {
            $value = $observation->get($variable);

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
     * @return mixed
     */
    public function nestComputedEquations($equation)
    {
        $variables = $this->getVariables($equation);
        $computed = $this->getComputedBenchmarks();

        foreach ($variables as $variable) {
            if (!empty($computed[$variable])) {
                $insideBenchmark = $computed[$variable];
                $insideEquation = $insideBenchmark->getEquation();

                // Recurse in case the inside equation contains other computed ones
                $insideEquation = $this->nestComputedEquations($insideEquation);

                $equation = str_replace(
                    '{{' . $variable . '}}',
                    '( ' . $insideEquation . ' )',
                    $equation
                );
            }
        }

        return $equation;
    }

    public function calculateAllForObservation(Observation $observation, Study $study)
    {
        $benchmarks = $this->getBenchmarkModel()->findComputed($study);

        foreach ($benchmarks as $benchmark) {
            if ($this->debug) {
                pr($benchmark->getName());
            }

            $this->calculate($benchmark, $observation);
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

    public function setObservationModel(ObservationkModel $observationModel)
    {
        $this->observationModel = $observationModel;

        return $this;
    }

    public function getObservationModel()
    {
        return $this->observationModel;
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

    public function getComputedBenchmarks()
    {
        if (empty($this->computedBenchmarks)) {
            $benchmarks = $this->getBenchmarkModel()->findComputed($this->getStudy());

            $computedBenchmarks = array();
            foreach ($benchmarks as $benchmark) {
                $computedBenchmarks[$benchmark->getDbColumn()] = $benchmark;
            }

            $this->computedBenchmarks = $computedBenchmarks;
        }

        return $this->computedBenchmarks;
    }
}
