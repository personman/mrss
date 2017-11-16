<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\CopyData as CD;

class CopyData implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $service = new CD();
        $currentStudy = $serviceManager->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        $subscriptionModel = $serviceManager->get('model.subscription');
        $service->setSubscriptionModel($subscriptionModel);

        $benchmarkModel = $serviceManager->get('model.benchmark');
        $service->setBenchmarkModel($benchmarkModel);

        return $service;
    }
}
