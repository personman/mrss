<?php

namespace Mrss;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        //$e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Set up model injector
        $sm = $e->getApplication()->getServiceManager();
        $sm->get('em')
            ->getEventManager()
            ->addEventListener(
                array(\Doctrine\ORM\Events::postLoad),
                new \Mrss\Service\ModelInjector($sm)
            );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(
                'em' => 'doctrine.entitymanager.orm_default',
            ),
            'invokables' => array(),
            'services' => array(),
            'factories' => array(
                'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
                'import.nccbp' => function ($sm) {
                    // Prepare the importer with the db to import from and the em
                    $nccbpDb = $sm->get('nccbp-db');
                    $em = $sm->get('em');
                    $importer = new \Mrss\Service\ImportNccbp($nccbpDb, $em);

                    // Inject the college Model
                    $collegeModel = $sm->get('model.college');
                    $importer->setCollegeModel($collegeModel);

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

                    // Inject settings
                    $settingModel = $sm->get('model.setting');
                    $importer->setSettingModel($settingModel);

                    return $importer;
                },
                // Perhaps there should be a generic model factory
                // That injects the em
                'model.college' => function ($sm) {
                    $collegeModel = new \Mrss\Model\College;
                    $em = $sm->get('em');

                    $collegeModel->setEntityManager($em);

                    return $collegeModel;
                },
                'model.observation' => function ($sm) {
                    $observationModel = new \Mrss\Model\Observation();
                    $em = $sm->get('em');

                    $observationModel->setEntityManager($em);

                    return $observationModel;
                },
                'model.benchmark' => function ($sm) {
                    $benchmarkModel = new \Mrss\Model\Benchmark();
                    $em = $sm->get('em');

                    $benchmarkModel->setEntityManager($em);

                    return $benchmarkModel;
                },
                'model.benchmarkGroup' => function ($sm) {
                    $benchmarkGroupkModel = new \Mrss\Model\BenchmarkGroup();
                    $em = $sm->get('em');

                    $benchmarkGroupkModel->setEntityManager($em);

                    return $benchmarkGroupkModel;
                },
                'model.study' => function ($sm) {
                    $studyModel = new \Mrss\Model\Study();
                    $em = $sm->get('em');

                    $studyModel->setEntityManager($em);

                    return $studyModel;
                },
                'model.setting' => function ($sm) {
                    $settingModel = new \Mrss\Model\Setting();
                    $em = $sm->get('em');

                    $settingModel->setEntityManager($em);

                    return $settingModel;
                },
                'service.formBuilder' => function ($sm) {
                    $service = new \Mrss\Service\FormBuilder;

                    return $service;
                }
            ),
        );
    }
}
