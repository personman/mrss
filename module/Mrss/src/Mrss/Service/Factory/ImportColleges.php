<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Mrss\Service\Import\College as CollegeImporter;

/**
 * Class ImportColleges
 *
 * @package Mrss\Service\Factory
 */
class ImportColleges implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $manager)
    {
        $service = new CollegeImporter();

        $collegeModel = $manager->get('model.college');
        $service->setCollegeModel($collegeModel);

        $systemModel = $manager->get('model.system');
        $service->setSystemModel($systemModel);

        return $service;
    }
}
