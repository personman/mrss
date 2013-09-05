<?php

return array(
    'routeCacheFile' => 'data/cache/cmsRoutes',
    'router' => array(
        'routes' => array(
            // @todo: get rid of this general route
            /*'general' => array(
                'type' => 'segment',
                'priority' => -10,
                'options' => array(
                    'route' => '/[:controller[/:action[/:id]]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'         => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'index',
                        'action' => 'index',
                        'id' => 0
                    )
                )
            ),/**/
            'home' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            ),

            // Data entry route with benchmarkGroup id
            // We could support a benchmarkGroup short name for nicer urls in the
            // future
            'data-entry' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/data-entry',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'overview',
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/:benchmarkGroup',
                            'constraints' => array(
                                'benchmarkGroup' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'action' => 'dataEntry',
                                'benchmarkGroup' => 0
                            )
                        )
                    ),
                    'import' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import',
                            'defaults' => array(
                                'action' => 'import'
                            )
                        )
                    ),
                    'export' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/export',
                            'defaults' => array(
                                'action' => 'export'
                            )
                        )
                    ),
                    'importsystem' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/importsystem',
                            'defaults' => array(
                                'action' => 'importsystem'
                            )
                        )
                    ),
                    'exportsystem' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/exportsystem',
                            'defaults' => array(
                                'action' => 'exportsystem'
                            )
                        )
                    ),
                    'switch' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/switch[/:college_id]',
                            'defaults' => array(
                                'action' => 'switch',
                                'college_id' => 0
                            )
                        )
                    )
                )
            ),
            'studies' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/studies',
                    'defaults' => array(
                        'controller' => 'studies',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/view/:id',
                            'defaults' => array(
                                'action' => 'view',
                                'id' => 0
                            )
                        )
                    ),
                    'completion' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/completion/:id',
                            'defaults' => array(
                                'action' => 'completion',
                                'id' => 0
                            )
                        )
                    ),
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0
                            )
                        )
                    ),
                    'import' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import/:id',
                            'defaults' => array(
                                'action' => 'import',
                                'id' => 0
                            )
                        )
                    )
                )
            ),
            'subscribe' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    'route' => '/subscribe',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'add'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'user-agreement' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/user-agreement',
                            'defaults' => array(
                                'action' => 'agreement'
                            )
                        )
                    ),
                    'payment' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/payment',
                            'defaults' => array(
                                'action' => 'payment'
                            )
                        )
                    ),
                    'invoice' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/invoice',
                            'defaults' => array(
                                'action' => 'invoice'
                            )
                        )
                    ),
                    'system' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/system',
                            'defaults' => array(
                                'action' => 'system'
                            )
                        )
                    ),
                    'complete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/complete',
                            'defaults' => array(
                                'action' => 'complete'
                            )
                        )
                    ),
                    'postback' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/postback',
                            'defaults' => array(
                                'action' => 'postback'
                            )
                        )
                    ),
                )
            ),
            'college' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/college-fake-route',
                    'defaults' => array(
                        'controller' => 'Mrss\Controller\College',
                        'action'     => 'index',
                    ),
                ),
            ),
            'colleges' => array(
                'type' => 'segment',
                'priority' => 10,
                'may_terminate' => true,
                'options' => array(
                    'route' => '/colleges',
                    'defaults' => array(
                        'controller' => 'colleges',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/view/:id',
                            'defaults' => array(
                                'action' => 'view',
                                'id' => 0
                            )
                        )
                    )
                )
            ),
            'systems' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/systems',
                    'defaults' => array(
                        'controller' => 'systems',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add',
                            'defaults' => array(
                                'action' => 'add'
                            )
                        )
                    ),
                    'view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/view/:id',
                            'defaults' => array(
                                'action' => 'view',
                                'id' => 0
                            )
                        )
                    ),
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'add',
                                'id' => 0
                            )
                        )
                    ),
                    'addcollege' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/addcollege/:system_id',
                            'defaults' => array(
                                'action' => 'addcollege',
                                'system_id' => 0
                            )
                        )
                    ),
                    'removecollege' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/removecollege/:college_id',
                            'defaults' => array(
                                'action' => 'removecollege',
                                'college_id' => 0
                            )
                        )
                    ),
                    'addadmin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/addadmin/:system_id',
                            'defaults' => array(
                                'action' => 'addadmin',
                                'system_id' => 0
                            )
                        )
                    ),
                    'removeadmin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/removeadmin/:user_id',
                            'defaults' => array(
                                'action' => 'removeadmin',
                                'user_id' => 0
                            )
                        )
                    ),
                )
            ),
            'benchmarks' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/benchmarks[/study/:study]',
                    'defaults' => array(
                        'controller' => 'benchmarks',
                        'study' => 1,
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/view/:id',
                            'defaults' => array(
                                'action' => 'view',
                                'id' => 0
                            )
                        )
                    ),
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0
                            )
                        )
                    ),
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add/benchmarkgroup/:benchmarkGroup',
                            'defaults' => array(
                                'action' => 'add',
                                'benchmarkGroup' => 0
                            )
                        )
                    )
                )
            ),
            'benchmarkgroups' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/benchmarkgroups',
                    'defaults' => array(
                        'controller' => 'benchmarkgroups',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0
                            )
                        )
                    ),
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add/study/:study',
                            'defaults' => array(
                                'action' => 'add',
                                'study' => 0
                            )
                        )
                    )
                )
            ),
            'observation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/observations/:id',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'view',
                        'id' => null
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'group' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/:benchmarkGroupId',
                            'constraints' => array(
                                'benchmarkGroupId' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'benchmarkGroupId' => 0
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'edit' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/edit',
                                    'defaults' => array(
                                        'action' => 'edit'
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            'users' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/users',
                    'defaults' => array(
                        'controller' => 'users',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/:id',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0
                            )
                        )
                    )
                )
            ),
            'settings' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/settings',
                    'defaults' => array(
                        'controller' => 'settings',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
            ),
            'glossary' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/glossary',
                    'defaults' => array(
                        'controller' => 'index',
                        'action' => 'glossary'
                    )
                )
            ),
            'import' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/import[/:action]',
                    'defaults' => array(
                        'controller' => 'import',
                        'action' => 'index'
                    )
                )
            ),
            'admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin',
                        'action' => 'dashboard'
                    )
                )
            ),
            // CMS:
            'cmsPage' => array(
                'type' => 'segment',
                'priority' => 1,
                'options' => array(
                    'route' => '/:pageRoute',
                    'constraints' => array(
                        'pageRoute' => 'dynamically-populated-by-bootstrap'
                    ),
                    'defaults' => array(
                        'controller' => 'pages',
                        'action' => 'view'
                    )
                )
            ),
            'pages' => array(
                'type' => 'segment',
                'priority' => 2,
                'options' => array(
                    'route' => '/pages[/:action[/:id]]',
                    'constraints' => array(
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'         => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'pages',
                        'action' => 'index',
                        'id' => 0
                    )
                )
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'mrss' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/mrss',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Mrss\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'import' => array(
                    'options' => array(
                        'route' => 'import <type>',
                        'defaults' => array(
                            'controller' => 'import',
                            'action' => 'background'
                        )
                    )
                ),
                'import2' => array(
                    'options' => array(
                        'route' => 'import2',
                        'defaults' => array(
                            'controller' => 'import',
                            'action' => 'test'
                        )
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'index' => 'Mrss\Controller\IndexController',
            'admin' => 'Mrss\Controller\AdminController',
            'import' => 'Mrss\Controller\ImportController',
            'colleges' => 'Mrss\Controller\CollegeController',
            'systems' => 'Mrss\Controller\SystemController',
            'observations' => 'Mrss\Controller\ObservationController',
            'benchmarks' => 'Mrss\Controller\BenchmarkController',
            'benchmarkgroups' => 'Mrss\Controller\BenchmarkGroupController',
            'subscription' => 'Mrss\Controller\SubscriptionController',
            'studies' => 'Mrss\Controller\StudyController',
            'settings' => 'Mrss\Controller\SettingController',
            'pages' => 'Mrss\Controller\PageController',
            'users' => 'Mrss\Controller\UserController',
            'EquationValidator' => '\Mrss\Validator\Equation'
        ),
        'factories' => array(
            // Override the contact controller
            'PhlyContact\Controller\Contact' => 'Mrss\Service\ContactControllerFactory',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            //'CurrentStudy' => 'Mrss\Controller\Plugin\CurrentStudy',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'mrss' => __DIR__ . '/../view',
        ),
    ),
    'view_manager' => array(
        // Hide error details by default. Use a local override in dev to show them
        'display_not_found_reason' => false,
        'display_exceptions'       => false,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'zfc-user/user/login' => __DIR__ . '/../view/mrss/user/login.phtml',
            'goalio-forgot-password/forgot/forgot' => __DIR__ .
            '/../view/mrss/user/forgot.phtml',
            'goalio-forgot-password/email/forgot' => __DIR__ .
                '/../view/mrss/email/forgot.phtml',
            'email/subscription/newuser' => __DIR__ .
                '/../view/mrss/email/newuser.phtml',
            // Override the PhlyContact view
            'phly-contact/contact/index' => __DIR__ .
            '/../view/mrss/contact/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Timestampable\TimestampableListener'
                ),
            ),
        ),
        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'my_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Mrss/Entity',
                ),
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'Mrss\Entity' => 'my_annotation_driver'
                )
            )
        ),
    ),
);
