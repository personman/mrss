<?php

namespace Mrss\Service\Factory;

use Mrss\Service\Import\ImportWorkforceData;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportWorkforceDataFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $service = new ImportWorkforceData();

        /*$collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $model = $sm->get('model.observation');
        $service->setObservationModel($model);*/

        $wfDb = $serviceManager->get('workforce-db');
        $service->setWfDb($wfDb);

        $service->setServiceManager($serviceManager);


        return $service;
    }
}
