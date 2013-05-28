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

        // the Calculation
        $result = $equation->evaluate();

        // Save the computed value
        $observation->set($benchmarkColumn, $result);
        $this->getObservationModel()->save($observation);
    }

    public function prepareEquation($equation, Observation $observation)
    {
        // Extract the variables
        preg_match_all('/{{(.*?)}}/', $equation, $matches);

        $variables = $matches[1];

        $equation = MathParser::build($equation);

        $vars = array();
        foreach ($variables as $variable) {
            $value = $observation->get($variable);
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
