<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\UserExport;

class ExportUsers implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $exporter = new UserExport();

        $subscriptionModel = $sm->get('model.subscription');
        $exporter->setSubscriptionModel($subscriptionModel);

        $collegeModel = $sm->get('model.college');
        $exporter->setCollegeModel($collegeModel);

        return $exporter;
    }
}
