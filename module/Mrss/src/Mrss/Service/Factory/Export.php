<?php

namespace Mrss\Service\Factory;

use Mrss\Service\DataExport as Exporter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
