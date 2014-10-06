<?php

namespace Mrss;

use Doctrine\Common\Proxy\Autoloader;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Mrss\View\Helper\FlashMessages;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();

        // Log exceptions and errors
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'handleError')
        );
        $eventManager->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'handleError')
        );
        Logger::registerErrorHandler($this->getErrorLog());



        // Set the timezone
        date_default_timezone_set('America/Chicago');

        //$e->getApplication()->getServiceManager()->get('translator');
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Set up model injector
        $sm = $e->getApplication()->getServiceManager();
        $sm->get('doctrine.entitymanager.orm_default')
            ->getEventManager()
            ->addEventListener(
                array(\Doctrine\ORM\Events::postLoad),
                new Service\ModelInjector($sm)
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

        $this->checkStudyAtLogin($e);
    }

    public function handleError(MvcEvent $e) {
        $logger = $this->getErrorLog();

        $exception = $e->getParam('exception');

        if (!empty($exception)) {
            $logger->err($exception->getMessage());
        } else {
            $logger->err('Error with no exception object.');
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $logger->info('IP: ' . $_SERVER['REMOTE_ADDR']);
        }

        // Log user identity, if present
        $userService = $e->getApplication()
            ->getServiceManager()->get('zfcuser_auth_service');

        // Set the current User
        $user = $userService->getIdentity();
        if ($user) {
            $logger->info("User: " . $user->getId());
        }
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
            // This doesn't seem to help performance, oddly
            /*'Zend\Loader\ClassMapAutoloader' => array(
                dirname(dirname(__DIR__)) . '/autoload_classmap.php',
            ),*/
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
                'nccbp_navigation' =>
                'Mrss\Service\NccbpNavigationFactory',
                'import.nccbp' => function ($sm) {
                    // Prepare the importer with the db to import from and the em
                    $nccbpDb = $sm->get('nccbp-db');
                    $em = $sm->get('em');
                    $importer = new Service\ImportNccbp($nccbpDb, $em);

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

                    // Inject the observation logger
                    $logger = $sm->get('service.observationAudit');
                    $importer->setObservationAudit($logger);

                    return $importer;
                },
                'service.variableSubstitution' => function ($sm) {
                    $service = new Service\VariableSubstitution();
                    $currentStudy = $sm->get('ControllerPluginManager')
                        ->get('currentStudy')->getCurrentStudy();
                    $service->setStudyYear($currentStudy->getCurrentYear());

                    return $service;
                },
                'import.nccwtp' => function ($sm) {
                    $nccwtp = new Service\ImportNccwtp();

                    return $nccwtp;
                },
                'import.csv' => function ($sm) {
                    $importer = new Service\ImportBenchmarks();

                    // Models
                    $benchmarkModel = $sm->get('model.benchmark');
                    $importer->setBenchmarkModel($benchmarkModel);

                    $benchmarkGroupModel = $sm->get('model.benchmarkGroup');
                    $importer->setBenchmarkGroupModel($benchmarkGroupModel);

                    $computedFieldsService = $sm->get('computedFields');
                    $importer->setComputedFieldsService($computedFieldsService);

                    $importer->setEntityManager($sm->get('em'));

                    return $importer;
                },
                'export' => function ($sm) {
                    $exportService = new Service\DataExport();

                    $studyModel = $sm->get('model.study');
                    $exportService->setStudyModel($studyModel);

                    $subscriptionModel = $sm->get('model.subscription');
                    $exportService->setSubscriptionModel($subscriptionModel);

                    return $exportService;
                },
                'export.nccbp' => function ($sm) {
                    $nccbpDb = $sm->get('nccbp-db');
                    $exporter = new Service\ExportNccbp($nccbpDb);

                    return $exporter;
                },
                'export.users' => function ($sm) {
                    $exporter = new Service\UserExport();

                    $subscriptionModel = $sm->get('model.subscription');
                    $exporter->setSubscriptionModel($subscriptionModel);

                    return $exporter;
                },
                'computedFields' => function ($sm) {
                    $computedFields = new Service\ComputedFields();

                    $benchmarkModel = $sm->get('model.benchmark');
                    $computedFields->setBenchmarkModel($benchmarkModel);

                    $observationModel = $sm->get('model.observation');
                    $computedFields->setObservationModel($observationModel);

                    $currentStudy = $sm->get('ControllerPluginManager')
                        ->get('currentStudy')->getCurrentStudy();
                    $computedFields->setStudy($currentStudy);

                    return $computedFields;
                },
                'validator.equation' => function ($sm) {
                    $validator = new Validator\Equation(
                        $sm->get('computedFields'),
                        $sm->get('model.benchmark')
                    );

                    return $validator;
                },
                // Perhaps there should be a generic model factory
                // That injects the em
                'model.user' => function ($sm) {
                    $userModel = new Model\User();
                    $em = $sm->get('em');

                    $userModel->setEntityManager($em);

                    return $userModel;
                },
                'model.college' => function ($sm) {
                    $collegeModel = new Model\College;
                    $em = $sm->get('em');

                    $collegeModel->setEntityManager($em);

                    return $collegeModel;
                },
                'model.ipedsInstitution' => function ($sm) {
                    $model = new Model\IpedsInstitution;
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.system' => function ($sm) {
                    $systemModel = new Model\System;
                    $em = $sm->get('em');

                    $systemModel->setEntityManager($em);

                    return $systemModel;
                },
                'model.observation' => function ($sm) {
                    $observationModel = new Model\Observation();
                    $em = $sm->get('em');

                    $observationModel->setEntityManager($em);

                    return $observationModel;
                },
                'model.subobservation' => function ($sm) {
                    $model = new Model\SubObservation();
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.changeSet' => function ($sm) {
                    $model = new Model\ChangeSet();
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.benchmark' => function ($sm) {
                    $benchmarkModel = new Model\Benchmark();
                    $em = $sm->get('em');

                    $benchmarkModel->setEntityManager($em);

                    return $benchmarkModel;
                },
                'model.benchmarkGroup' => function ($sm) {
                    $benchmarkGroupkModel = new Model\BenchmarkGroup();
                    $em = $sm->get('em');

                    $benchmarkGroupkModel->setEntityManager($em);

                    return $benchmarkGroupkModel;
                },
                'model.study' => function ($sm) {
                    $studyModel = new Model\Study();
                    $em = $sm->get('em');

                    $studyModel->setEntityManager($em);

                    return $studyModel;
                },
                'model.offerCode' => function ($sm) {
                    $offerCodeModel = new Model\OfferCode();
                    $em = $sm->get('em');

                    $offerCodeModel->setEntityManager($em);

                    return $offerCodeModel;
                },
                'model.peerGroup' => function ($sm) {
                    $model = new Model\PeerGroup();
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.subscription' => function ($sm) {
                    $subscriptionModel = new Model\Subscription();
                    $em = $sm->get('em');

                    $subscriptionModel->setEntityManager($em);

                    return $subscriptionModel;
                },
                'model.subscriptionDraft' => function ($sm) {
                    $model = new Model\SubscriptionDraft();
                    $em = $sm->get('em');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.setting' => function ($sm) {
                    $settingModel = new Model\Setting();
                    $em = $sm->get('em');

                    $settingModel->setEntityManager($em);

                    return $settingModel;
                },
                'model.page' => function ($sm) {
                    $pageModel = new Model\Page;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $pageModel->setEntityManager($em);

                    return $pageModel;
                },
                'model.percentile' => function ($sm) {
                    $model = new Model\Percentile;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.percentileRank' => function ($sm) {
                    $model = new Model\PercentileRank;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.outlier' => function ($sm) {
                    $model = new Model\Outlier;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $model->setEntityManager($em);

                    return $model;
                },
                'model.payment' => function ($sm) {
                    $paymentModel = new Model\Payment;
                    $em = $sm->get('doctrine.entitymanager.orm_default');

                    $paymentModel->setEntityManager($em);

                    return $paymentModel;
                },
                'service.routeCache' => function ($sm) {
                    $routeCacheService = new Service\RouteCache;

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
                    $service = new Service\FormBuilder;

                    return $service;
                },
                'service.observationAudit' => function ($sm) {
                    $service = new Service\ObservationAudit;
                    $userService = $sm->get('zfcuser_auth_service');
                    $impersonationService = $sm->get('zfcuserimpersonate_user_service');
                    
                    // Set the current User
                    $user = $userService->getIdentity();
                    $service->setUser($user);

                    // If there's an admin impersonating this user, pass that
                    if ($impersonationService->isImpersonated()) {
                        $impersonator = $impersonationService
                            ->getStorageForImpersonator()->read();
                        //prd($impersonator);

                        $service->setImpersonator($impersonator);
                    }

                    // The current study
                    $currentStudy = $sm->get('ControllerPluginManager')
                        ->get('currentStudy')->getCurrentStudy();
                    $service->setStudy($currentStudy);

                    // The benchmark model
                    $service->setBenchmarkModel($sm->get('model.benchmark'));

                    // Changeset model
                    $service->setChangeSetModel($sm->get('model.changeSet'));

                    return $service;
                },
                'service.nhebisubscriptions' => function ($sm) {
                    $config = $sm->get('Config');
                    $service = new Service\NhebiSubscriptions();

                    if (!empty($config['nhebisubscriptions'])) {
                        $service->setConfiguration($config['nhebisubscriptions']);
                    }

                    return $service;
                },
                'service.nhebisubscriptions.mrss' => function ($sm) {
                    // Use require to avoid namespaces so this class can run in old PHP
                    //$file = dirname(__FILE__) . '/src/Mrss/Service/NhebiSubscriptions/Mrss.php';
                    //require $file;

                    $service = new Service\NhebiSubscriptions\Mrss();
                    $service->setCollegeModel($sm->get('model.college'));
                    $service->setSubscriptionModel($sm->get('model.subscription'));

                    return $service;
                },
                'service.report.calculator' => function ($sm) {
                    $service = new Service\Report\Calculator();

                    return $service;
                },
                'service.report' => function ($sm) {
                    $currentStudy = $sm->get('ControllerPluginManager')
                        ->get('currentStudy')->getCurrentStudy();

                    $service = new Service\Report();

                    $service->setSubscriptionModel($sm->get('model.subscription'));
                    $service->setStudy($currentStudy);
                    $service->setCalculator($sm->get('service.report.calculator'));
                    $service->setPercentileModel($sm->get('model.percentile'));
                    $service->setPercentileRankModel(
                        $sm->get('model.percentileRank')
                    );
                    $service->setBenchmarkModel($sm->get('model.benchmark'));
                    $service->setCollegeModel($sm->get('model.college'));
                    $service->setSettingModel($sm->get('model.setting'));
                    $service->setOutlierModel($sm->get('model.outlier'));
                    $service->setComputedFieldsService($sm->get('computedFields'));
                    $service->setMailTransport($sm->get('mail.transport'));

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
                    $plugin = new Controller\Plugin\CurrentStudy;

                    // Inject the study model so we can look up the study entity
                    $studyModel = $sm->getServiceLocator()->get('model.study');
                    $plugin->setStudyModel($studyModel);

                    $request = $sm->getServiceLocator()
                        ->get('request');
                    if (method_exists($request, 'getUri')) {
                        // The current base url
                        $url = $request
                            ->getUri()
                            ->getHost();
                        $plugin->setUrl($url);
                    } else {
                        $plugin->setUrl('console');
                    }

                    $config = $sm->getServiceLocator()->get('Config');

                    if (empty($config)) {
                        throw new \Exception('Study url config is missing');
                    }

                    $studyConfig = $config['studies'];

                    $plugin->setConfig($studyConfig);

                    return $plugin;
                },
                'currentObservation' => function ($sm) {
                    $plugin = new Controller\Plugin\CurrentObservation();
                    $model = $sm->getServiceLocator()->get('model.observation');
                    $plugin->setObservationModel($model);
                    $plugin->setCurrentStudyPlugin($sm->get('currentStudy'));
                    $plugin->setCurrentCollegePlugin($sm->get('currentCollege'));

                    return $plugin;
                },
                'currentCollege' => function ($sm) {
                    $plugin = new Controller\Plugin\CurrentCollege();
                    $model = $sm->getServiceLocator()->get('model.college');
                    $plugin->setCollegeModel($model);
                    $plugin->setuserPlugin($sm->get('zfcUserAuthentication'));

                    return $plugin;
                },
                'systemActiveCollege' => function ($sm) {
                    $plugin = new Controller\Plugin\SystemActiveCollege;

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
                    $helper = new View\Helper\CurrentStudy;
                    $helper->setPlugin($plugin);

                    return $helper;
                },
                'systemAdmin' => function($sm) {
                    $helper = new View\Helper\SystemAdmin;

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
                'chart' => function($sm) {
                    $helper = new View\Helper\Chart;

                    return $helper;
                 },
                'simpleFormElement' => function($sm) {
                    $helper = new View\Helper\SimpleFormElement;

                    return $helper;
                }
            ),
        );
    }

    /**
     * Make sure a user isn't logging into a study they don't have access to
     *
     * @param MvcEvent $e
     */
    public function checkStudyAtLogin(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $zfcServiceEvents = $serviceManager
            ->get('ZfcUser\Authentication\Adapter\AdapterChain')->getEventManager();

        $zfcServiceEvents->attach(
            'authenticate',
            function ($e) use ($serviceManager) {
                $params = $e->getParams();
                $userId = $params['identity'];
                if ($userId) {

                    $userModel = $serviceManager->get('model.user');
                    $user = $userModel->find($userId);

                    $cpm = $serviceManager->get('ControllerPluginManager');
                    $currentStudy = $cpm->get('currentStudy')->getCurrentStudy();

                    if (!$user->hasStudy($currentStudy)) {
                        $cpm->get('flashMessenger')->addErrorMessage(
                            'You do not have access to this study. If you believe
                            this is incorrect, please contact us.'
                        );
                        header('Location: /');
                        die();
                    }
                }
            }
        );
    }

    protected function getErrorLog()
    {
        $logger = new Logger;
        $writer = new Stream('error.log');
        $logger->addWriter($writer);

        return $logger;
    }
}
