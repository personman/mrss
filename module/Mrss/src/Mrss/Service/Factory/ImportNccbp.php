<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ImportNccbp as Import;

class ImportNccbp implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        // Prepare the importer with the db to import from and the em
        $nccbpDb = $sm->get('nccbp-db');
        $em = $sm->get('em');
        $importer = new Import($nccbpDb, $em);

        // Inject the college Model
        $collegeModel = $sm->get('model.college');
        $importer->setCollegeModel($collegeModel);

        // Inject the user Model
        $userModel = $sm->get('model.user');
        $importer->setUserModel($userModel);

        // Inject the observation model
        $observationModel = $sm->get('model.observation');
        $importer->setObservationModel($observationModel);

        // Inject the benchmark model
        $benchmarkModel = $sm->get('model.benchmark');
        $importer->setBenchmarkModel($benchmarkModel);

        // Inject the benchmarkGroup model
        $benchmarkGroupModel = $sm->get('model.benchmarkGroup');
        $importer->setBenchmarkGroupModel($benchmarkGroupModel);

        // Inject the study model
        $studyModel = $sm->get('model.study');
        $importer->setStudyModel($studyModel);

        // Inject the subscription model
        $subscriptionModel = $sm->get('model.subscription');
        $importer->setSubscriptionModel($subscriptionModel);

        // Inject the system model
        $systemModel = $sm->get('model.system');
        $importer->setSystemModel($systemModel);

        // Inject settings
        $settingModel = $sm->get('model.setting');
        $importer->setSettingModel($settingModel);

        // Inject the peer group model
        $peerGroupModel = $sm->get('model.peerGroup');
        $importer->setPeerGroupModel($peerGroupModel);

        // Inject the observation logger
        $logger = $sm->get('service.observationAudit');
        $importer->setObservationAudit($logger);

        return $importer;
    }
}
