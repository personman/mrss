<?php

namespace Mrss\Service;

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

        // Analysis first.
        $this->displayData($observations);



    }

    public function displayData($observations)
    {
        $html = "<table>\n";
        $html .= "<tr><th></th>";

        // Headers
        foreach ($observations as $collegeId => $data) {
            $college = $this->getCollegeModel()->find($collegeId);
            $html .= "<th>" . $college->getName() . "</th>";
        }
        $html .= "</tr>\n";

        // Data
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $html .= "<tr>";
            $html .= "<td>" . $benchmark->getName() . "</td>";

            foreach ($observations as $collegeId => $observation) {
                $value = $observation->get($benchmark->getDbColumn());

                $class = 'normal';
                if ($value === null) {
                    $class = 'null';
                }
                $html .= "<td class='$class'>" . $value . "</td>";
            }

            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "<style>.null { background: #CCC }</style>";

        echo $html;
    }
}
