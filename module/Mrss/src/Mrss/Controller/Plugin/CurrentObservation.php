<?php

namespace Mrss\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Mrss\Entity\Observation;
use Mrss\Model\Observation as ObservationModel;

/**
 * Class CurrentObservation
 *
 * The dataset we are working with.
 *
 * @package Mrss\Controller\Plugin
 */
class CurrentObservation extends AbstractPlugin
{
    /**
     * @var ObservationModel
     */
    protected $observationModel;

    /**
     * @var CurrentStudy
     */
    protected $currentStudyPlugin;

    /**
     * @var CurrentCollege
     */
    protected $currentCollegePlugin;

    protected $studyConfig;

    /**
     * @return Observation
     */
    public function __invoke($year = null)
    {
        return $this->getCurrentObservation($year);
    }

    public function getCurrentObservation($year = null)
    {
        // Find the observation by the year and the user's college
        $collegeId = $this->getCurrentCollegePlugin()->getCurrentCollege()->getId();

        if ($year === null) {
            $config = $this->getStudyConfig();
            if ($config->use_structures) {
                die('missing year in plugin CurrentObservation, line 51');
            } else {
                $year = $this->getCurrentStudyPlugin()->getCurrentStudy()->getCurrentYear();
            }

        }

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $this->getObservationModel()->findOne($collegeId, $year);

        if (empty($observation)) {
            throw new \Exception("Unable to get current observation (plugin) for college $collegeId and year $year.");
        }

        return $observation;
    }

    /**
     * @param ObservationModel $observationModel
     * @returns CurrentObservation
     */
    public function setObservationModel($observationModel)
    {
        $this->observationModel = $observationModel;

        return $this;
    }

    /**
     * @return ObservationModel
     */
    public function getObservationModel()
    {
        return $this->observationModel;
    }

    /**
     * @param CurrentStudy $currentStudyPlugin
     */
    public function setCurrentStudyPlugin($currentStudyPlugin)
    {
        $this->currentStudyPlugin = $currentStudyPlugin;
    }

    /**
     * @return CurrentStudy
     */
    public function getCurrentStudyPlugin()
    {
        return $this->currentStudyPlugin;
    }

    /**
     * @param CurrentCollege $currentCollegePlugin
     */
    public function setCurrentCollegePlugin($currentCollegePlugin)
    {
        $this->currentCollegePlugin = $currentCollegePlugin;
    }

    /**
     * @return CurrentCollege
     */
    public function getCurrentCollegePlugin()
    {
        return $this->currentCollegePlugin;
    }

    /**
     * @return mixed
     */
    public function getStudyConfig()
    {
        return $this->studyConfig;
    }

    /**
     * @param mixed $studyConfig
     * @return CurrentObservation
     */
    public function setStudyConfig($studyConfig)
    {
        $this->studyConfig = $studyConfig;
        return $this;
    }
}
