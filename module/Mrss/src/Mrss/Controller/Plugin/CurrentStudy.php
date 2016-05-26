<?php

namespace Mrss\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Mrss\Entity\Study;
use Mrss\Model\Study as StudyModel;

/**
 * Class CurrentStudy
 *
 * Determine the current study based on the config and url. Look the study up in the
 * db and return it.
 *
 * @package Mrss\Controller\Plugin
 */
class CurrentStudy extends AbstractPlugin
{
    /** @var Study */
    protected $study;

    /** @var StudyModel */
    protected $studyModel;

    protected $config;

    protected $url;

    /**
     * @return Study
     */
    public function __invoke()
    {
        return $this->getCurrentStudy();
    }

    public function getCurrentStudy()
    {
        if (empty($this->study)) {
            // Get the study id by the url
            $studiesConfig = $this->getConfig();

            $url = $this->getUrl();

            if ($url == '192.232.207.42') {
                die;
            }

            // Does the url match a config option?
            if (!empty($studiesConfig[$url])) {
                $studyId = $studiesConfig[$url];

                $study = $this->getStudyModel()->find($studyId);

                if (!empty($study)) {
                    $this->study = $study;
                } else {
                    throw new \Exception(
                        "Unable to find current study: $studyId."
                    );
                }
            } else {
                throw new \Exception("Unable to determine current study from url: $url");
            }
        }

        return $this->study;
    }

    public function setStudyModel(StudyModel $studyModel)
    {
        $this->studyModel = $studyModel;
    }

    public function getStudyModel()
    {
        return $this->studyModel;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
