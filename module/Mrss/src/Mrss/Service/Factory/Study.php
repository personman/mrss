<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class Study implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Load the default study config
        $studyConfig = new Config(include 'config/studies/study.default.php', true);

        // Override with study-specivic config
        $currentStudy = $serviceLocator->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $studyId = $currentStudy->getId();

        if ($studyConfigArray = include "config/studies/study.$studyId.php") {
            $specificStudyConfig = new Config($studyConfigArray);
            $studyConfig->merge($specificStudyConfig);
        }

        return $studyConfig;
    }
}
