<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\CopyData as CD;

class CopyData implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new CD();

        $subscriptionModel = $sm->get('model.subscription');
        $service->setSubscriptionModel($subscriptionModel);

        $benchmarkModel = $sm->get('model.benchmark');
        $service->setBenchmarkModel($benchmarkModel);

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
