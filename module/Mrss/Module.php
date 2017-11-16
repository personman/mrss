<?php

namespace Mrss;

use Doctrine\Common\Proxy\Autoloader;
use Zend\Log\Formatter\Simple;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Mrss\View\Helper\FlashMessages;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Navigation\Page\Mvc;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Config\Config;
use Zend\Cache\StorageFactory;

class Module
{
    static $registeredErrorHandler;
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();

        $this->bootstrapSession($e);

        $this->setLayout($e);

        // Log exceptions and errors
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'handleError')
        );
        $eventManager->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'handleError')
        );

        $config = $sm->get('Config');

        if (!empty($config['log_error_backtrace'])) {
            self::registerErrorHandler($this->getErrorLog());
        } else {
            Logger::registerErrorHandler($this->getErrorLog());
        }

        if (!empty($config['enable_sql_logger'])) {
            // Touch the sql logger, so it works
            //$collector = $sm->get('doctrine.sql_logger_collector.orm_default');
        }



        // Set the timezone
        date_default_timezone_set('America/Chicago');

        //$e->getApplication()->getServiceManager()->get('translator');
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Set up model injector
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
            if ($auth->hasIdentity() && $user = $auth->getIdentity()) {
                $user->setLastAccess(new \DateTime('now'));

                $userModel = $sm->get('model.user');
                $userModel->save($user);
                $sm->get('em')->flush();
            }
        });

        $this->checkStudyAtLogin($e);

        try {
            $this->setupTitle($e);
            $this->setupNavigation($e);
        } catch (\Exception $e) {

        }
    }

    public function setupNavigation(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        // Add ACL information to the Navigation view helper
        $authorize = $sm->get('BjyAuthorizeServiceAuthorize');
        $acl = $authorize->getAcl();
        $role = $authorize->getIdentity();
        \Zend\View\Helper\Navigation::setDefaultAcl($acl);
        \Zend\View\Helper\Navigation::setDefaultRole($role);

    }

    public function bootstrapSession(MvcEvent $e)
    {
        // 7 days
        $sessionSeconds = 60 * 60 * 24 * 7;

        $manager = new SessionManager();

        $manager->getConfig()
            ->setOption('gc_maxlifetime', $sessionSeconds);
        $manager->getConfig()
            ->setOption('remember_me_seconds', $sessionSeconds);
        $manager->rememberMe($sessionSeconds);

        $r = $manager->getConfig()->getOption('remember_me_seconds');

        Container::setDefaultManager($manager);
    }

    public function setLayout(MvcEvent $e)
    {
        $e->getApplication()->getEventManager()->getSharedManager()
            ->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
                    $controller = $e->getTarget();

                    $studyConfig = $controller->getServiceLocator()->get('study');
                    $layout = $studyConfig->layout;
                    $controller->layout('layout/' . $layout);
                }, 100);
    }

    public function setupTitle(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $cpm = $serviceManager->get('ControllerPluginManager');
        $currentStudy = $cpm->get('currentStudy')->getCurrentStudy();

        // Getting the view helper manager from the application service manager
        $viewHelperManager = $e->getApplication()->getServiceManager()
            ->get('viewHelperManager');

        // Getting the headTitle helper from the view helper manager
        $headTitleHelper   = $viewHelperManager->get('headTitle');

        // Setting a separator string for segments
        $headTitleHelper->setSeparator(' - ');

        $studyName = $currentStudy->getDescription();

        // Remove leading "the "
        if (substr($studyName, 0, 4) == 'the ') {
            $studyName = substr($studyName, 4);
        }

        // Setting the action, controller, module and site name as title segments
        $headTitleHelper->append($studyName);

    }

    public function handleError(MvcEvent $e) {
        // Log user identity, if present
        $userService = $e->getApplication()
            ->getServiceManager()->get('zfcuser_auth_service');
        $user = $userService->getIdentity();

        $message = '';

        $exception = $e->getParam('exception');

        $error = $e->getError();
        // error-unauthorized-controller
        // error-router-no-match

        if ($error == 'error-unauthorized-controller') {
            // Don't log unauthorized. They just need to log in.
            return false;
        }

        $message .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' | ';

        $logIt = true;
        if ($error == 'error-router-no-match') {
            $message .= 'Not found.';
            $logIt = false;
        } elseif (!empty($exception)) {
            $message .= $exception->getMessage();
        } else {
            if ($error) {
                $message .= $error;
            } else {
                $message .= "Unknown error.";
            }
        }

        // Set the current User
        if ($user) {
            $message .= " | User: " . $user->getEmail();
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            //$message .= " | User-agent: " . $_SERVER['HTTP_USER_AGENT'];
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $message .= " | IP: " . $_SERVER['REMOTE_ADDR'];
        }



        if ($logIt) {
            $this->getErrorLog(true)->err($message);
        }
    }

    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @link http://www.php.net/manual/function.set-error-handler.php
     * @param  Logger $logger
     * @param  bool   $continueNativeHandler
     * @return mixed  Returns result of set_error_handler
     * @throws Exception\InvalidArgumentException if logger is null
     */
    public static function registerErrorHandler(Logger $logger, $continueNativeHandler = false)
    {
        // Only register once per instance
        if (static::$registeredErrorHandler) {
            return false;
        }

        $errorPriorityMap = static::$errorPriorityMap;

        $previous = set_error_handler(function ($level, $message, $file, $line) use ($logger, $errorPriorityMap, $continueNativeHandler) {
            $iniLevel = error_reporting();

            if ($iniLevel & $level) {
                if (isset($errorPriorityMap[$level])) {
                    $priority = $errorPriorityMap[$level];
                } else {
                    $priority = Logger::INFO;
                }

                $trace = print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10), 1);

                $logger->log($priority, $message, array(
                    'errno'   => $level,
                    'file'    => $file,
                    'line'    => $line,
                    'trace'   => $trace
                ));
            }

            return !$continueNativeHandler;
        });

        static::$registeredErrorHandler = true;
        return $previous;
    }


    /**
     * Map native PHP errors to priority
     *
     * @var array
     */
    public static $errorPriorityMap = array(
        E_NOTICE            => Logger::NOTICE,
        E_USER_NOTICE       => Logger::NOTICE,
        E_WARNING           => Logger::WARN,
        E_CORE_WARNING      => Logger::WARN,
        E_USER_WARNING      => Logger::WARN,
        E_ERROR             => Logger::ERR,
        E_USER_ERROR        => Logger::ERR,
        E_CORE_ERROR        => Logger::ERR,
        E_RECOVERABLE_ERROR => Logger::ERR,
        E_STRICT            => Logger::DEBUG,
        E_DEPRECATED        => Logger::DEBUG,
        E_USER_DEPRECATED   => Logger::DEBUG,
    );


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
            'Zend\Loader\ClassMapAutoloader' => array(
                dirname(dirname(__DIR__)) . '/autoload_classmap.php',
            ),
            /*'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),*/
        );
    }

    /**
     * Remember to run php bin/classmap_generator.php after adding a new class
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(
                'Factory' => 'Mrss\Service\Report\Factory',
                'ModelFactory' => 'Mrss\Service\Factory\Model'
            ),
            'aliases' => array(
                'em' => 'doctrine.entitymanager.orm_default',
            ),
            'invokables' => array(),
            'services' => array(),
            'factories' => array(
                'navigation' => 'Mrss\Service\NavigationFactory',
                'footer_navigation' => 'Mrss\Service\FooterNavigationFactory',
                'admin_navigation' => 'Mrss\Service\AdminNavigationFactory',
                'nccbp_navigation' => 'Mrss\Service\NccbpNavigationFactory',
                'envisio_navigation' => 'Envisio\Service\NavigationFactory',
                'envisiousernavigation' => 'Envisio\Service\UserNavigationFactory',
                'fcs_navigation' => 'Mrss\Service\FcsNavigationFactory',
                'study' => 'Mrss\Service\Factory\Study',
                'import.nccbp' => 'Mrss\Service\Factory\ImportNccbp',
                'service.observationAudit' => 'Mrss\Service\Factory\ObservationAudit',
                'computedFields' => 'Mrss\Service\Factory\ComputedFields',
                'copyData' => 'Mrss\Service\Factory\CopyData',
                'service.validation' => 'Mrss\Service\Factory\Validation',
                'import.csv' => 'Mrss\Service\Factory\ImportBenchmarks',
                'export' => 'Mrss\Service\Factory\Export',
                'import.nccwtp' => 'Mrss\Service\Factory\ImportNccwtp',
                'export.nccbp' => 'Mrss\Service\Factory\ExportNccbp',
                'export.users' => 'Mrss\Service\Factory\ExportUsers',
                'validator.equation' => 'Mrss\Service\Factory\EquationValidator',
                'download.colleges' => 'Mrss\Service\Factory\DownloadColleges',
                'service.import.colleges' => 'Mrss\Service\Factory\ImportColleges',
                'service.import.colleges.demo' => 'Mrss\Service\Factory\ImportCollegeDemographics',
                'service.import.data' => 'Mrss\Service\Factory\ImportData',
                'service.import.colleges.category' => 'Mrss\Service\Factory\ImportCollegeCategory',
                'service.import.users' => 'Mrss\Service\Factory\ImportUsers',
                'service.import.workforce.data' => 'Mrss\Service\Factory\ImportWorkforceDataFactory',
                'service.observation.data.migration' => 'Mrss\Service\Factory\ObservationDataMigration',
                'service.variableSubstitution' => function ($sm) {
                    $service = new Service\VariableSubstitution();
                    $currentStudy = $sm->get('ControllerPluginManager')
                        ->get('currentStudy')->getCurrentStudy();
                    $service->setStudyYear($currentStudy->getCurrentYear());

                    return $service;
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

                    $variable = $sm->get('service.variableSubstitution');
                    $service->setVariableSubstitutionService($variable);

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
                'service.merge.data' => function ($sm) {
                    $service = new Service\MergeData();
                    $service->setObservationModel($sm->get('model.observation'));
                    $service->setCollegeModel($sm->get('model.college'));
                    $plugin = $sm->get('ControllerPluginManager')
                        ->get('currentStudy');
                    $study = $plugin->getCurrentStudy();

                    $service->setStudy($study);

                    //$service = new Service\Report\Calculator();

                    return $service;
                },
                'cache' => function() {
                    return StorageFactory::factory(
                        array(
                            'adapter' => array(
                                'name' => 'filesystem',
                                'options' => array(
                                    'dirLevel' => 2,
                                    'cacheDir' => 'data/cache',
                                    'dirPermission' => 0755,
                                    'filePermission' => 0666,
                                    'namespaceSeparator' => '-db-'
                                ),
                            ),
                            'plugins' => array('serializer'),
                        )
                    );
                },
                'mail.transport' => function ($sm) {
                    $config = $sm->get('config');

                    // Get from the shared config
                    $smtpConfig = $config['goaliomailservice']['transport_options'];

                    $transport = new \Zend\Mail\Transport\Smtp();
                    $options = new \Zend\Mail\Transport\SmtpOptions($smtpConfig);

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
                    $plugin->setStudyConfig($sm->getServiceLocator()->get('study'));

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

                    $helper->setConfig($sm->getServiceLocator()->get('study'));

                    return $helper;
                },
                'studyConfig' => function($sm) {
                    $helper = new View\Helper\StudyConfig();
                    $helper->setConfig($sm->getServiceLocator()->get('study'));

                    return $helper;
                },
                'muut' => function($sm) {
                    $helper = new View\Helper\Muut();
                    $studyConfig = $sm->getServiceLocator()->get('study');
                    $helper->setConfig($studyConfig->muut);

                    return $helper;
                },
                'systemAdmin' => function($sm) {
                    $helper = new View\Helper\SystemAdmin;

                    // Inject the user
                    $auth = $sm->getServiceLocator()->get('zfcuser_auth_service');
                    if ($auth->hasIdentity()) {
                        $user = $auth->getIdentity();
                        $helper->setUser($user);
                    }

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

                    $systemModel = $sm->getServiceLocator()->get('model.system');
                    $helper->setSystemModel($systemModel);

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

                if (!empty($params['identity'])) {
                    $userId = $params['identity'];

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

    protected function getErrorLog($shortFormat = false)
    {
        $formatter = new Simple('%message% -- %extra%' . PHP_EOL);

        $writer = new Stream('error.log');

        if ($shortFormat) {
            $writer->setFormatter($formatter);
        }

        $logger = new Logger;
        $logger->addWriter($writer);

        return $logger;
    }
}
