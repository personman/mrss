<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\DataExport as Exporter;

class Export implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceManager
     * @return Exporter
     */
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $exportService = new Exporter();

        $studyModel = $serviceManager->get('model.study');
        $exportService->setStudyModel($studyModel);

        $subscriptionModel = $serviceManager->get('model.subscription');
        $exportService->setSubscriptionModel($subscriptionModel);

        return $exportService;
    }
}
