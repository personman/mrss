<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use Mrss\Entity\PercentChange;

class Changes extends Report
{
    protected $percentThreshold = 5;

    protected $percentChangeModel;

    public function calculateChanges($observationId)
    {
        if ($observation = $this->getObservationModel()->find($observationId)) {
            $year = $observation->getYear();
            $this->clearChangesForCollege($observation->getCollege(), $year);

            // Now get the previous year's observation
            $previousYear = $year - 1;

            $collegeId = $observation->getCollege()->getId();

            if ($previousObservation = $this->getObservationModel()->findOne($collegeId, $previousYear)) {
                // Now loop over all benchmarks
                $benchmarks = $this->getStudy()->getAllBenchmarks();
                foreach ($benchmarks as $benchmark) {
                    $dbColumn = $benchmark->getDbColumn();
                    $value = $observation->get($dbColumn);
                    $previousValue = $previousObservation->get($dbColumn);

                    if (!is_null($value) && !is_null($previousValue) && $previousValue != 0) {
                        $percentDifference = $this->compareValues($value, $previousValue);

                        if (abs($percentDifference) >= $this->percentThreshold)
                        $this->recordChange($value, $previousValue, $benchmark, $observation, $percentDifference);
                    }
                }
            }
        }

        // Persist
        $this->getPercentChangeModel()->getEntityManager()->flush();
    }

    public function compareValues($value, $previousValue)
    {
        $difference = $previousValue - $value;

        $percentDifference = $difference / $previousValue * 100;

        return $percentDifference;
    }

    public function recordChange($value, $previousValue, $benchmark, $observation, $percentDifference)
    {
        //pr($value);pr($previousValue);pr($benchmark->getDescriptiveReportLabel());pr($percentDifference);
        $change = new PercentChange();
        $change->setValue($value);
        $change->setOldValue($previousValue);
        $change->setPercentChange($percentDifference);
        $change->setBenchmark($benchmark);
        $change->setYear($observation->getYear());
        $change->setCollege($observation->getCollege());
        $change->setStudy($this->getStudy());

        $this->getPercentChangeModel()->save($change);
    }

    public function clearChangesForCollege($college, $year)
    {
        $this->getPercentChangeModel()->deleteByCollegeAndYear($college, $year);
    }

    /**
     * @return \Mrss\Model\PercentChange
     */
    public function getPercentChangeModel()
    {
        return $this->percentChangeModel;
    }

    /**
     * @param mixed $percentChangeModel
     * @return Changes
     */
    public function setPercentChangeModel($percentChangeModel)
    {
        $this->percentChangeModel = $percentChangeModel;
        return $this;
    }
}
