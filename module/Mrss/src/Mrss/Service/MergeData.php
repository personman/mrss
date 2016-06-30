<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;

class MergeData
{
    protected $study;

    protected $year;

    protected $observationModel;

    protected $collegeModel;


    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return \Mrss\Model\Observation
     */
    public function getObservationModel()
    {
        return $this->observationModel;
    }

    /**
     * @param mixed $observationModel
     * @return MergeData
     */
    public function setObservationModel($observationModel)
    {
        $this->observationModel = $observationModel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    /**
     * @param mixed $collegeModel
     * @return MergeData
     */
    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;
        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param mixed $study
     * @return MergeData
     */
    public function setStudy($study)
    {
        $this->study = $study;
        return $this;
    }


    /**
     * @param $from Array of college ids
     * @param $to One college id.
     */
    public function merge($from, $to)
    {
        //echo $this->getStudy()->getName();

        $benchmarks = $this->getStudy()->getAllBenchmarks();

        // Get the observations
        $observations = array();
        foreach ($from as $collegeId) {
            $observation = $this->getObservationModel()->findOne($collegeId, $this->getYear());
            $observations[$collegeId] = $observation;
        }

        $mergedData = $this->mergeData($benchmarks, $observations);

        // Analysis first.
        $this->displayData($observations, $mergedData);


        // Get the target observation
        $targetObservation = $this->getObservationModel()->findOne($to, $this->getYear());

        // Save the merged data
        foreach ($mergedData as $dbColumn=> $value) {
            $targetObservation->set($dbColumn, $value);
        }
        $this->getObservationModel()->getEntityManager()->flush();


    }

    /**
     * @param Benchmark[] $benchmarks
     * @param $observations
     * @return array
     */
    public function mergeData($benchmarks, $observations)
    {
        $mergedData = array();

        foreach ($benchmarks as $benchmark) {
            $dbColumn = $benchmark->getDbColumn();

            // Skip computed fields
            /*if ($benchmark->getComputed()) {
                $mergedData[$dbColumn] = null;
            }*/


            // Sum
            $sum = null;

            $count = 0;
            foreach ($observations as $observation) {
                if ($value = $observation->get($dbColumn)) {
                    $sum += $value;
                    $count++;
                }
            }

            if ($this->shouldAverage($benchmark)) {
                $sum = $sum / $count;
            }

            $mergedData[$dbColumn] = $sum;
        }

        return $mergedData;
    }

    protected function shouldAverage(Benchmark $benchmark)
    {
        return true;

        /*$should = false;
        if ($benchmark->getBenchmarkGroup()->getId() == 1) {
            $should = true;
        }

        if ($benchmark->isPercent()) {
            $should = true;
        }

        return $should;*/
    }

    public function displayData($observations, $mergedData)
    {
        $html = "<table>\n";
        $html .= "<tr><th></th>";

        // Headers
        foreach ($observations as $collegeId => $data) {
            $college = $this->getCollegeModel()->find($collegeId);
            $html .= "<th>" . $college->getName() . "</th>";
        }

        $html .= "<th>Merged</th>";

        $html .= "</tr>\n";

        // Data
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $html .= "<tr>";
            $html .= "<td>" . $benchmark->getName() . "</td>";

            foreach ($observations as $collegeId => $observation) {
                $dbColumn = $benchmark->getDbColumn();
                $value = $observation->get($dbColumn);

                $class = 'normal';
                if ($value === null) {
                    $class = 'null';
                }
                $html .= "<td class='$class'>" . $value . "</td>";
            }

            $html .= "<td>" . $mergedData[$dbColumn] . "</td>";

            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "<style>.null { background: #CCC }</style>";

        echo $html;
    }
}
