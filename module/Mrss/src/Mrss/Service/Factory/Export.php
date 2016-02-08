<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\DataExport as Exporter;

class Export implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $exportService = new Exporter();

        $studyModel = $sm->get('model.study');
        $exportService->setStudyModel($studyModel);

        $subscriptionModel = $sm->get('model.subscription');
        $exportService->setSubscriptionModel($subscriptionModel);

        return $exportService;

    }
}
