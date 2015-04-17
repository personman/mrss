<?php

namespace Mrss\Service\Report\Max;

use Mrss\Entity\Observation;

abstract class ActivityReport extends National
{
    abstract protected function getTopLevelBenchmarkKey($activity);
    abstract protected function getDetailColumns($activity);

    public function getData(Observation $observation)
    {
        $this->setObservation($observation);

        $activities = $this->getActivities();

        $data = array();
        foreach ($activities as $activity => $label) {
            $topLevelKey = $this->getTopLevelBenchmarkKey($activity);

            $benchmark = $this->getBenchmark($topLevelKey);

            $benchmarkData = $this->getBenchmarkData($benchmark);
            $benchmarkData['benchmark'] = $label;
            $benchmarkData['details'] = $this->getDetails($activity);

            $data[] = $benchmarkData;
        }

        return $data;
    }

    protected function getDetails($activity)
    {
        $dbColumns = $this->getDetailColumns($activity);
        $details = array();

        foreach ($dbColumns as $dbColumn => $label) {
            $benchmark = $this->getBenchmark($dbColumn);

            if (is_object($benchmark)) {
                $benchmarkData = $this->getBenchmarkData($benchmark);

                // Set the label
                $benchmarkData['benchmark'] = $label;

                $details[] = $benchmarkData;
            } else {
                echo "<p>Unable to find benchmark for $dbColumn ";
            }

        }


        return $details;
    }
}
