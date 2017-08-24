<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ComputedFields as CF;

class ComputedFields implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $computedFields = new CF();

        $benchmarkModel = $sm->get('model.benchmark');
        $computedFields->setBenchmarkModel($benchmarkModel);

        $observationModel = $sm->get('model.observation');
        $computedFields->setObservationModel($observationModel);

        $subObservationModel = $sm->get('model.subObservation');
        $computedFields->setSubObservationModel($subObservationModel);

        $studyConfig = $sm->get('study');
        $computedFields->setStudyConfig($studyConfig);

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $computedFields->setStudy($currentStudy);

        return $computedFields;
    }
}
