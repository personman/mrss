<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;

class AbstractValidation
{
    /** @var  Observation $observation */
    protected $observation;

    protected $priorYearObservation;

    protected $benchmarkModel;

    protected $issues = array();

    public function setObservation(Observation $observation)
    {
        $this->observation = $observation;

        return $this;
    }

    public function runValidation($observation, $priorYearObservation = null)
    {
        $this->setObservation($observation);
        $this->priorYearObservation = $priorYearObservation;

        foreach ($this->getMethodNames() as $method) {
            $this->$method();
        }

        return $this->getIssues();
    }

    public function getMethodNames()
    {
        $prefix = 'validate';
        $names = array();

        foreach (get_class_methods($this) as $methodName) {
            if (substr($methodName, 0, strlen($prefix)) == $prefix) {
                $names[] = $methodName;
            }
        }

        return $names;
    }

    /**
     *
     * @param $message
     */
    protected function addIssue($message, $errorCode, $formUrl = null)
    {
        $this->issues[] = array(
            'message' => $message,
            'formUrl' => $formUrl,
            'errorCode' => $errorCode
        );
    }

    public function getIssues()
    {
        return $this->issues;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }
}
