<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\CollegeCategory;

class ImportCollegeCategory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new CollegeCategory();

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $model = $sm->get('model.observation');
        $service->setObservationModel($model);

        return $service;
    }
}
