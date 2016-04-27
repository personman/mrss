<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\CollegeDemographics;

class ImportCollegeDemographics implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new CollegeDemographics();

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $model = $sm->get('model.observation');
        $service->setObservationModel($model);

        return $service;
    }
}
