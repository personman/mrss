<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\College as CollegeImporter;

class ImportColleges implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new CollegeImporter();

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $systemModel = $sm->get('model.system');
        $service->setSystemModel($systemModel);

        return $service;
    }
}
