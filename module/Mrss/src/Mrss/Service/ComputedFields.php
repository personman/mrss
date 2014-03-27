<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Observation;
use Mrss\Model\Benchmark as  BenchmarkModel;
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

    public function calculate(Benchmark $benchmark, Observation $observation)
    {
        $equation = $benchmark->getEquation();
        $benchmarkColumn = $benchmark->getDbColumn();

        if (empty($equation)) {
            return false;
        }

        // Populate variables
        $equation = $this->prepareEquation($equation, $observation);

        if (empty($equation)) {
            /*throw new \Exception(
                'Invalid equation for benchmark: .' . $benchmark->getDbColumn())
            ;*/
            return false;
        }

        // the Calculation
        $result = $equation->evaluate();

        // If the result is meant to be a percentage, multiply by 100
        if ($benchmark->getInputType() == 'percent' ||
            $benchmark->getInputType() == 'wholepercent') {
            $result = $result * 100;
        }

        // Save the computed value
        $observation->set($benchmarkColumn, $result);
        $this->getObservationModel()->save($observation);
        $this->getObservationModel()->getEntityManager()->flush();

        return true;
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

    public function prepareEquation($equation, Observation $observation)
    {
        $variables = $this->getVariables($equation);

        $equation = $this->buildEquation($equation);

        $vars = array();
        foreach ($variables as $variable) {
            $value = $observation->get($variable);

            // If any of the variables are null or '', bail out
            if ($value === null || $value === '') {
                return false;
            }

            $vars[$variable] = $value;
        }

        return $equation->setVars($vars);
    }

    public function calculateAllForObservation(Observation $observation)
    {
        $benchmarks = $this->getBenchmarkModel()->findComputed();

        foreach ($benchmarks as $benchmark) {
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
}
