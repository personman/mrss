<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\User as UserImport;

class ImportUsers implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new UserImport();

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $userModel = $sm->get('model.user');
        $service->setUserModel($userModel);

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
