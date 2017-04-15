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

        $observationModel = $sm->get('model.observation');
        $service->setObservationModel($observationModel);

        $benchmarkModel = $sm->get('model.benchmark');
        $service->setBenchmarkModel($benchmarkModel);

        $datumModel = $sm->get('model.datum');
        $service->setDatumModel($datumModel);

        $collegeModel = $sm->get('model.college');
        $service->setCollegeModel($collegeModel);

        $model = $sm->get('model.system');
        $service->setSystemModel($model);

        $membershipModel = $sm->get('model.system.membership');
        $service->setSystemMembershipModel($membershipModel);

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        return $service;
    }
}
