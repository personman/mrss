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

    protected $debugDbColumn = null;

    protected $skipEmpty = true;

    protected $error;

    protected $keyedBenchmarks = array();

    protected $variableService;

    public function calculate(
        Benchmark $benchmark,
        Observation $observation,
        $flush = true,
        SubObservation $subObservation = null
    ) {
        if ($this->getDebug()) {
            $start = microtime(1);
        }

        // Respect the compute if values missing checkbox
        $this->skipEmpty = !$benchmark->getComputeIfValuesMissing();

        $equationWithVariables = $benchmark->getEquation();
        $benchmarkColumn = $benchmark->getDbColumn();
        if ($this->getDebug()) {
            //echo "equation prepared: " . round(microtime(1) - $start, 3) . "s<br>";
        }

        if (empty($equationWithVariables)) {
            $result = null;
        } else {

            if ($this->getDebug()) {
                $collegeName = $observation->getCollege()->getName();
                echo 'Institution:';
                pr($collegeName);

                echo 'Equation:';
                pr($equationWithVariables);
            }

            // Populate variables
            $this->recursionLevel = 0;
            $equationWithVariables = $this
                ->nestComputedEquations(
                    $equationWithVariables,
                    $observation->getYear()
                );

            if ($this->getDebug()) {
                echo 'Expanded equation:';
                pr($equationWithVariables);

                $equationWithNumbers = $this->getEquationWithNumbers($benchmark, $observation);

                echo 'Equation with numbers:';
                pr($equationWithNumbers);
            }

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

        if ($this->getDebug()) {
            echo "Result = $result. ";
            //echo "About to flush (if applicable): " . round(microtime(1) - $start, 3) . "s<br>";
            echo '<hr>';

            if (false && $this->debugDbColumn) {

                $oids = array();
                foreach ($this->getStudy()->getSubscriptionsForYear($this->getStudy()->getCurrentYear()) as $sub) {
                    $collegeName = $sub->getCollege()->getName();
                    $obId = $sub->getObservation()->getId();

                    echo "<a href='/reports/compute-one/$obId/1/{$benchmark->getDbColumn()}'>$collegeName</a><br>";
                }


            }
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
            if ($this->getDebug()) {
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

        $variables = array_unique($variables);

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
                // As long as there's no division or multiplication involved, we can assume nulls are 0
                if (!$this->skipEmpty || ((strpos($equation, '/') === false && strpos($equation, '*') === false))) {
                    $value = 0;
                } else {
                    $errors[] = "Missing variable: $variable. ";

                    continue;
                }
            }

            $vars[$variable] = $value;
        }

        if (!empty($errors) && !$this->debug) {
            return false;
        }


        if ($this->getDebug()) {
            //pr($observation->getId());
            //pr($variables);

            echo 'Errors:';
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
            if ($this->getDebug()) {
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

    protected $recursionLevel = 0;

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
        $this->recursionLevel++;

        $variables = $this->getVariables($equation);
        $computed = $this->getComputedBenchmarks($year);

        if ($this->getDebug()) {
            echo 'Variables: ';
            pr($variables);
        }

        foreach ($variables as $variable) {
            if (!empty($computed[$variable])) {
                $insideBenchmark = $computed[$variable];
                $insideEquation = $insideBenchmark->getEquation();

                if ($this->getDebug()) {
                    echo 'Inside equation: ';
                    pr($insideEquation);
                }

                // Multiply by 100 for percentages
                if ($insideBenchmark->isPercent()) {
                    $insideEquation = "($insideEquation * 100)";
                }

                // Recurse in case the inside equation contains other computed ones
                if ($this->recursionLevel > 15) {
                    if ($this->getDebug()) {
                        echo 'Equation nesting reached maximum recursion level: 15. ';
                    }
                    return '';
                } else {
                    $insideEquation = $this->nestComputedEquations($insideEquation, $year);
                }

                $equation = str_replace(
                    '{{' . $variable . '}}',
                    '( ' . $insideEquation . ' )',
                    $equation
                );
            }
        }

        $this->recursionLevel--;
        return $equation;
    }

    public function calculateAllForObservation(Observation $observation)
    {
        $flushEvery = 3000;
        if (empty($observation)) {
            throw new \Exception('Observation missing.');
        }

        $null = $observation->getSubscription()->getAllData();

        $col = $this->debugDbColumn;
        $benchmarks = $this->getComputedBenchmarks($observation->getYear());

        // Load the data
        $sub = $observation->getSubscription();

        //foreach ($sub->getData() as $datum) {
            //$val = $datum->getValue();
        //}

        $i = 0;
        foreach ($benchmarks as $benchmark) {
            $i++;

            if ($col && $benchmark->getDbColumn() != $col) {
                continue;
            }

            if ($this->getDebug()) {
                pr($benchmark->getName());
            }

            try {
                $this->calculate($benchmark, $observation, false);
            } catch (\Exception $e) {
                //throw new \Exception('calculation problem');
                //pr($e->getMessage());
                //prd($e);
                continue;
            }

            if ($i % $flushEvery == 0) {
                $this->getObservationModel()->getEntityManager()->flush();
            }

            //if ($i > 50) break;
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

        return $this;
    }

    public function getEquationWithLabels(Benchmark $benchmark, $nested = true)
    {
        $equation = $benchmark->getEquation();

        if ($nested) {
            $equation = $this->nestComputedEquations($equation, $this->getStudy()->getCurrentYear());
        }

        $variables = $this->getVariables($equation);

        foreach ($variables as $variable) {
            if ($benchmarkToInsert = $this->getBenchmarkByDbColumn($variable)) {
                /** @var \Mrss\Entity\Benchmark $benchmarkToInsert */
                //$benchmarkToInsert = $keyedBenchmarks[$variable];

                $fieldName = $benchmarkToInsert->getDescriptiveReportLabel();
                $fieldName = $this->getVariableService()->substitute($fieldName);
                /*$fieldName = "<span class='fieldName'>
                    $fieldName <a href='#'><span class='glyphicon glyphicon-edit icon-edit'></span></a>
                </span> "; */

                // Replace the dbColumn in the equation with a field name
                $variableWithBraces = '{{' . $variable . '}}';
                $equation = str_replace(
                    $variableWithBraces,
                    $fieldName,
                    $equation
                );
            }
        }

        return $equation;
    }

    public function getEquationWithNumbers(Benchmark $benchmark, Observation $observation, $nested = true)
    {
        $equation = $benchmark->getEquation();

        if ($nested) {
            $equation = $this->nestComputedEquations($equation, $this->getStudy()->getCurrentYear());
        }

        $variables = $this->getVariables($equation);

        foreach ($variables as $variable) {
            //$fieldName = $benchmarkToInsert->getDescriptiveReportLabel();
            //$fieldName = "<span class='fieldName'>$fieldName</span>";
            $value = $observation->get($variable);
            if ($value === null) {
                if ($this->skipEmpty) {
                    $value = 'null';
                } else {
                    $value = 0;
                }
            }

            // Replace the dbColumn in the equation with a value
            $variableWithBraces = '{{' . $variable . '}}';
            $equation = str_replace(
                $variableWithBraces,
                $value,
                $equation
            );
        }

        return $equation;

    }

    protected function getBenchmarkByDbColumn($dbColumn)
    {
        if (empty($this->keyedBenchmarks)) {
            $year = $this->getStudy()->getCurrentYear();
            $benchmarks = array();

            foreach ($this->getStudy()->getBenchmarksForYear($year) as $benchmark) {
                $benchmarks[$benchmark->getDbColumn()] = $benchmark;
            }

            $this->keyedBenchmarks = $benchmarks;
        }

        if (!empty($this->keyedBenchmarks[$dbColumn])) {
            return $this->keyedBenchmarks[$dbColumn];
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

    public function setDebugDbColumn($dbColumn)
    {
        $this->debugDbColumn = $dbColumn;

        return $this;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function setVariableService($service)
    {
        $this->variableService = $service;

        return $this;
    }

    public function getVariableService()
    {
        return $this->variableService;
    }
}
