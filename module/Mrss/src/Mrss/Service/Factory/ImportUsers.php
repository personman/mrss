<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\User as UserImport;

class ImportUsers implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $service = new UserImport();

        $collegeModel = $serviceManager->get('model.college');
        $service->setCollegeModel($collegeModel);

        $userModel = $serviceManager->get('model.user');
        $service->setUserModel($userModel);

        $currentStudy = $serviceManager->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
