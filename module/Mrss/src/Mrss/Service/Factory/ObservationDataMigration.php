<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use \Mrss\Service\ObservationDataMigration as Service;

class ObservationDataMigration implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new Service();
        $service->setDatumModel($sm->get('model.datum'));

        $study = $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($study);

        return $service;
    }
}
