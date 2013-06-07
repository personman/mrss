<?php

namespace Mrss;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Mrss\View\Helper\FlashMessages;

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
                'admin_navigation' =>
                'Mrss\Service\AdminNavigationFactory',
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
                'import.nccwtp' => function ($sm) {
                    $nccwtp = new \Mrss\Service\ImportNccwtp();

                    return $nccwtp;
                },
                'import.csv' => function ($sm) {
                    $importer = new \Mrss\Service\ImportBenchmarks();

                    // Models
                    $benchmarkModel = $sm->get('model.benchmark');
                    $importer->setBenchmarkModel($benchmarkModel);

                    $benchmarkGroupModel = $sm->get('model.benchmarkGroup');
                    $importer->setBenchmarkGroupModel($benchmarkGroupModel);

                    $importer->setEntityManager($sm->get('em'));

                    return $importer;
                },
                'computedFields' => function ($sm) {
                    $computedFields = new \Mrss\Service\ComputedFields();

                    $benchmarkModel = $sm->get('model.benchmark');
                    $computedFields->setBenchmarkModel($benchmarkModel);

                    $observationModel = $sm->get('model.observation');
                    $computedFields->setObservationModel($observationModel);

                    return $computedFields;
                },
                'validator.equation' => function ($sm) {
                    $validator = new \Mrss\Validator\Equation(
                        $sm->get('computedFields'),
                        $sm->get('model.benchmark')
                    );

                    return $validator;
                },
                // Perhaps there should be a generic model factory
                // That injects the em
                'model.user' => function ($sm) {
                    $userModel = new \Mrss\Model\User();
                    $em = $sm->get('em');

                    $userModel->setEntityManager($em);

                    return $userModel;
                },
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
                'model.subscription' => function ($sm) {
                    $subscriptionModel = new \Mrss\Model\Subscription();
                    $em = $sm->get('em');

                    $subscriptionModel->setEntityManager($em);

                    return $subscriptionModel;
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
                },
                'mail.transport' => function ($sm) {
                    //return new \Zend\Mail\Transport\Sendmail();
                    // @todo: Consider merging this with the GoalioMailService

                    $transport = new \Zend\Mail\Transport\Smtp();
                    $options = new \Zend\Mail\Transport\SmtpOptions(
                        array(
                            'host' => 'smtp.gmail.com',
                            'connection_class' => 'login',
                            'connection_config' => array(
                                'ssl' => 'tls',
                                'username' => 'dan.ferguson.mo@gmail.com',
                                'password' => 'nhebiemail'
                            ),
                            'port' => 587
                        )
                    );

                    $transport->setOptions($options);

                    return $transport;
                }
            ),
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'currentStudy' => function ($sm) {
                    $plugin = new \Mrss\Controller\Plugin\CurrentStudy;

                    // Inject the study model so we can look up the study entity
                    $studyModel = $sm->getServiceLocator()->get('model.study');
                    $plugin->setStudyModel($studyModel);

                    // The current base url
                    $url = $sm->getServiceLocator()
                        ->get('request')
                        ->getUri()
                        ->getHost();
                    $plugin->setUrl($url);

                    $config = $sm->getServiceLocator()->get('Config');
                    $studyConfig = $config['studies'];
                    $plugin->setConfig($studyConfig);

                    return $plugin;
                },
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'flashMessages' => function($sm) {
                    $flashmessenger = $sm->getServiceLocator()
                        ->get('ControllerPluginManager')
                        ->get('flashmessenger');

                    $messages = new FlashMessages();
                    $messages->setFlashMessenger($flashmessenger);

                    return $messages;
                },
                'currentStudy' => function($sm) {
                    // First get the controller plugin
                    $plugin = $sm->getServiceLocator()
                        ->get('ControllerPluginManager')
                        ->get('currentStudy');

                    // Now inject the plugin
                    $helper = new \Mrss\View\Helper\CurrentStudy;
                    $helper->setPlugin($plugin);

                    return $helper;
                }
            ),
        );
    }
}
