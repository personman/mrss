<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Import\Data;

class ImportData implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new Data();

        $subscriptionModel = $sm->get('model.subscription');
        $service->setSubscriptionModel($subscriptionModel);

        $benchmarkModel = $sm->get('model.benchmark');
        //$service->setBenchmarkModel($benchmarkModel);

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
