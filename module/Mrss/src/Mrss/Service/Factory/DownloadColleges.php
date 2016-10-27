<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\DownloadColleges as DC;

class DownloadColleges implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $service = new DC();

        $collegeModel = $serviceManager->get('model.college');
        $service->setCollegeModel($collegeModel);

        /*
        $observationModel = $sm->get('model.observation');
        $computedFields->setObservationModel($observationModel);

        $subObservationModel = $sm->get('model.subObservation');
        $computedFields->setSubObservationModel($subObservationModel);
        */

        $currentStudy = $serviceManager->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
