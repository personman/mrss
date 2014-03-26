<?php

namespace Mrss;

use Doctrine\Common\Proxy\Autoloader;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Mrss\View\Helper\FlashMessages;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        // Set the timezone
        date_default_timezone_set('America/Chicago');

        //$e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Set up model injector
        $sm = $e->getApplication()->getServiceManager();
        $sm->get('doctrine.entitymanager.orm_default')
            ->getEventManager()
            ->addEventListener(
                array(\Doctrine\ORM\Events::postLoad),
                new \Mrss\Service\ModelInjector($sm)
            );

        // Log access time
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($e) {
            $sm = $e->getApplication()->getServiceManager();
            $auth = $sm->get('zfcuser_auth_service');
            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();
                $user->setLastAccess(new \DateTime('now'));

                $userModel = $sm->get('model.user');
                $userModel->save($user);
                $sm->get('em')->flush();
            }
        });
    }

    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';

        // Get the cms page routes from a cache file
        if (!empty($config['routeCacheFile'])
            && file_exists($config['routeCacheFile'])) {
            $cachedRoutes = file_get_contents($config['routeCacheFile']);

            $config['router']['routes']['cmsPage']['options']
            ['constraints']['pageRoute'] = $cachedRoutes;
        }

        return $config;
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
                'navigation' => 'Mrss\Service\NavigationFactory',
                'footer_navigation' => 'Mrss\Service\FooterNavigationFactory',
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

                    // Inject the subscription model
                    $subscriptionModel = $sm->get('model.subscription');
                    $importer->setSubscriptionModel($subscriptionModel);

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
                'export' => function ($sm) {
                    $exportService = new \Mrss\Service\DataExport();

                    $studyModel = $sm->get('model.study');
                    $exportService->setStudyModel($studyModel);

                    $subscriptionModel = $sm->get('model.subscription');
                    $exportService->setSubscriptionModel($subscriptionModel);

                    return $exportService;
                },
                'export.nccbp' => function ($sm) {
                    $nccbpDb = $sm->get('nccbp-db');
                    $exporter = new \Mrss\Service\ExportNccbp($nccbpDb);

                    return $exporter;
                },
                'export.users' => function ($sm) {
                    $exporter = new \Mrss\Service\UserExport();

                    $subscriptionModel = $sm->get('model.subscription');
                    $exporter->setSubscriptionModel($subscriptionModel);

                    return $exporter;
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
                'model.ipedsInstitution' => function ($sm) {
                    $model = new \Mrss\Model\IpedsInstitution;
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.system' => function ($sm) {
                    $systemModel = new \Mrss\Model\System;
                    $em = $sm->get('em');

                    $systemModel->setEntityManager($em);

                    return $systemModel;
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
                'model.offerCode' => function ($sm) {
                        $offerCodeModel = new \Mrss\Model\OfferCode();
                        $em = $sm->get('em');

                        $offerCodeModel->setEntityManager($em);

                        return $offerCodeModel;
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
                'model.page' => function ($sm) {
                    $pageModel = new \Mrss\Model\Page;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $pageModel->setEntityManager($em);

                    return $pageModel;
                },
                'model.payment' => function ($sm) {
                    $paymentModel = new \Mrss\Model\Payment;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $paymentModel->setEntityManager($em);

                    return $paymentModel;
                },
                'service.routeCache' => function ($sm) {
                    $routeCacheService = new \Mrss\Service\RouteCache;

                    // Inject the page model
                    $pageModel = $sm->get('model.page');
                    $routeCacheService->setPageModel($pageModel);

                    // Grab the cache file from the config and pass it in
                    $config = $sm->get('Config');
                    if (!empty($config['routeCacheFile'])) {
                        $routeCacheService->setCacheFile($config['routeCacheFile']);
                    }

                    return $routeCacheService;
                },
                'service.formBuilder' => function ($sm) {
                    $service = new \Mrss\Service\FormBuilder;

                    return $service;
                },
                'service.nhebisubscriptions' => function ($sm) {
                    $config = $sm->get('Config');
                    $service = new \Mrss\Service\NhebiSubscriptions();

                    if (!empty($config['nhebisubscriptions'])) {
                        $service->setConfiguration($config['nhebisubscriptions']);
                    }

                    return $service;
                },
                'service.nhebisubscriptions.mrss' => function ($sm) {
                    // Use require to avoid namespaces so this class can run in old PHP
                    //$file = dirname(__FILE__) . '/src/Mrss/Service/NhebiSubscriptions/Mrss.php';
                    //require $file;

                    $service = new \Mrss\Service\NhebiSubscriptions\Mrss();
                    $service->setCollegeModel($sm->get('model.college'));
                    $service->setSubscriptionModel($sm->get('model.subscription'));

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

                    if (empty($config)) {
                        throw new \Exception('Study url config is missing');
                    }

                    $studyConfig = $config['studies'];

                    $plugin->setConfig($studyConfig);

                    return $plugin;
                },
                'systemActiveCollege' => function ($sm) {
                    $plugin = new \Mrss\Controller\Plugin\SystemActiveCollege;

                    // Inject the session container
                    $session = new \Zend\Session\Container('system_admin');
                    $plugin->setSessionContainer($session);

                    // Inject the college model
                    $collegeModel = $sm->getServiceLocator()->get('model.college');
                    $plugin->setCollegeModel($collegeModel);

                    return $plugin;
                }
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
                },
                'systemAdmin' => function($sm) {
                    $helper = new \Mrss\View\Helper\SystemAdmin;

                    // Inject the user
                    $auth = $sm->getServiceLocator()->get('zfcuser_auth_service');
                    $user = $auth->getIdentity();
                    $helper->setUser($user);

                    // Inject the controller plugin
                    $plugin = $sm->getServiceLocator()
                        ->get('ControllerPluginManager')
                        ->get('systemActiveCollege');
                    $helper->setActiveCollegePlugin($plugin);

                    // Inject the current study plugin
                    $plugin = $sm->getServiceLocator()
                        ->get('ControllerPluginManager')
                        ->get('currentStudy');
                    $helper->setCurrentStudyPlugin($plugin);

                    return $helper;
                },
                'simpleFormElement' => function($sm) {
                    $helper = new \Mrss\View\Helper\SimpleFormElement;

                    return $helper;
                }
            ),
        );
    }
}
