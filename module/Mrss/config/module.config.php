<?php

return array(
    'routeCacheFile' => 'data/cache/cmsRoutes',
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                'Mrss' => __DIR__ . '/../public',
            ),
        ),
        'caching' => array(
            'default' => array(
                'cache' => 'AssetManager\\Cache\\FilePathCache',
                'options' => array(
                    'dir' => 'public'
                )
            )
        )
    ),
    'router' => array(
        'routes' => array(
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

            'members' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/members',
                    'defaults' => array(
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            ),

            'submitted-values' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/submitted-values[/:year][/:format]',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'submittedValues',
                        'year' => null,
                        'format' => 'html'
                    )
                )
            ),
            'network-switch' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/network-switch/:systemId',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'dataEntrySwitch',
                        'systemId' => null
                    )
                ),
            ),

            // Data entry route with benchmarkGroup id
            // We could support a benchmarkGroup short name for nicer urls in the
            // future
            'data-entry-overview' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/data-entry',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'overview',
                        'year' => 0
                    )
                ),
            ),
            'data-entry' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/data-entry/:year',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'overview',
                        'year' => 0
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/:benchmarkGroup',
                            'defaults' => array(
                                'action' => 'dataEntry',
                                'benchmarkGroup' => 0,
                            )
                        ),
                        'child_routes' => array(
                            /*'subob' => array(
                                'type' => 'segment',
                                'may_terminate' => true,
                                'options' => array(
                                    'route' => '/:subId',
                                    'constraints' => array(
                                        'benchmarkGroup' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'edit',
                                        'controller' => 'subobservations',
                                        'subId' => 0,
                                    )
                                ),
                                'child_routes' => array(
                                    'delete' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/delete',
                                            'defaults' => array(
                                                'action' => 'delete'
                                            )
                                        )
                                    )
                                )
                            ),*/
                            'check' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/check',
                                    'defaults' => array(
                                        'action' => 'check',
                                        'controller' => 'subobservations'
                                    )
                                )
                            ),

                        )
                    ),
                    'import' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import',
                            'defaults' => array(
                                'action' => 'import',
                                //'year' => 0
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
                    'print' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/print[/:showData]',
                            'defaults' => array(
                                'action' => 'print',
                                'showData' => false
                            )
                        )
                    ),
                    'template' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/template[/:year]',
                            'defaults' => array(
                                'action' => 'template',
                                'year' => 0
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
                    ),
                    'all' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/all',
                            'defaults' => array(
                                'action' => 'all',
                            )
                        )
                    ),
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
                    ),
                    'export' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/export/:id',
                            'defaults' => array(
                                'action' => 'export',
                                'id' => 0
                            )
                        )
                    ),
                    'info' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/info',
                            'defaults' => array(
                                'action' => 'info',
                            )
                        )
                    )
                )
            ),
            'sections' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/modules/study/:study',
                    'defaults' => array(
                        'controller' => 'sections',
                        'action' => 'index',
                        'study' => null
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => null
                            )
                        )
                    ),
                ),
            ),
            'data-dictionary' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/data-dictionary',
                    'defaults' => array(
                        'controller' => 'studies',
                        'action' => 'dictionary'
                    )
                )
            ),
            'calculations' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/calculations',
                    'defaults' => array(
                        'controller' => 'studies',
                        'action' => 'calculations'
                    )
                )
            ),
            'offercodes' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/offercodes/study/:study',
                    'defaults' => array(
                        'controller' => 'offercodes',
                        'action' => 'index',
                        'study' => 0
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit[/id/:id]',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0
                            )
                        )
                    ),
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete[/id/:id]',
                            'defaults' => array(
                                'action' => 'delete',
                                'id' => 0
                            )
                        )
                    )
                )
            ),
            'subscription-delete' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/subscription/delete/:id',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'delete',
                        'id' => 0
                    )
                )

            ),
            'membership' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/membership[/:paymentMethod]',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'view',
                        'paymentMethod' => null
                    )
                )
            ),
            'membership-edit' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/membership-edit',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'edit'
                    )
                )
            ),
            'join' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    'route' => '/join',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'add'
                    )
                )
            ),
            'join-free' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    'route' => '/participate',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'joinFree'
                    )
                )
            ),
            'joined' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    'route' => '/joined',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'joined'
                    )
                )
            ),
            'find-user' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/find-user/:email',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'findUser',
                        'email' => null
                    )
                )
            ),
            'issues' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/issues',
                    'defaults' => array(
                        'controller' => 'issues',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'note' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/note',
                            'defaults' => array(
                                'action' => 'note'
                            )
                        )
                    ),
                    'staff' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/staff',
                            'defaults' => array(
                                'action' => 'staff'
                            )
                        )
                    ),
                    'download-users' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/download-users',
                            'defaults' => array(
                                'action' => 'downloadUsers'
                            )
                        )
                    ),
                    'update' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/update',
                            'defaults' => array(
                                'action' => 'update'
                            )
                        )
                    ),
                    'mass-update' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/mass-update',
                            'defaults' => array(
                                'action' => 'massUpdate'
                            )
                        )
                    )
                )
            ),
            'agreement' => array(
                'type' => 'segment',
                'priority' => 10,
                'options' => array(
                    'route' => '/agreement',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'agreementPublic'
                    )
                ),
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
                    'modules' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/modules',
                            'defaults' => array(
                                'action' => 'modules'
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
                    'free' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/free',
                            'defaults' => array(
                                'action' => 'free'
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
                    'cancel' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/cancel',
                            'defaults' => array(
                                'action' => 'cancel'
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
                    'check' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/check',
                            'defaults' => array(
                                'action' => 'check'
                            )
                        )
                    ),
                )
            ),
            'renew' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/renew',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'renew'
                    )
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
            'peer-institutions' => array(
                'type' => 'segment',
                'priority' => 10,
                'may_terminate' => true,
                'options' => array(
                    'route' => '/peers',
                    'defaults' => array(
                        'controller' => 'colleges',
                        'action' => 'peers'
                    )
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
                    ),
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add',
                            'defaults' => array(
                                'action' => 'add',
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
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete/:id',
                            'defaults' => array(
                                'action' => 'delete',
                                'id' => 0
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
                    'import-demo' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import-demo[/:service]',
                            'defaults' => array(
                                'action' => 'import',
                                'service' => 'demo'
                            )
                        )
                    ),
                    'download' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/download',
                            'defaults' => array(
                                'action' => 'download',
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
                    'editmember' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/editmember/:system_id/:college_id',
                            'defaults' => array(
                                'action' => 'addcollege',
                                'college_id' => 0,
                                'system_id' => 0
                            )
                        )
                    ),
                    'addadmin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/addadmin/:system_id[/:role]',
                            'defaults' => array(
                                'action' => 'addadmin',
                                'system_id' => 0,
                                'role' => 'system_admin'
                            )
                        )
                    ),
                    'removeadmin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/removeadmin/:user_id/:system_id/:role',
                            'defaults' => array(
                                'action' => 'removeadmin',
                                'user_id' => 0,
                                'system_id' => 0,
                                'role' => null
                            )
                        )
                    ),
                )
            ),
            'structures' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/structures',
                    'defaults' => array(
                        'controller' => 'structures',
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
                                'id' => null
                            )
                        )
                    ),
                    'save' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/save',
                            'defaults' => array(
                                'action' => 'save',
                            )
                        )
                    ),
                )
            ),
            'benchmark' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/benchmark[/study/:study]',
                    'defaults' => array(
                        'controller' => 'benchmarks',
                        'study' => 0,
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
                    'data' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/data/:id/:year',
                            'defaults' => array(
                                'action' => 'data',
                                'id' => 0,
                                'year' => null
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
                    ),
                    'equation' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/equation',
                            'defaults' => array(
                                'action' => 'equation'
                            )
                        )
                    ),
                    'check-equation' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/check-equation',
                            'defaults' => array(
                                'action' => 'checkEquation'
                            )
                        )
                    ),
                    'reorder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reorder',
                            'defaults' => array(
                                'action' => 'reorder'
                            )
                        )
                    ),
                    'on-report' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/on-report/:id/:value',
                            'defaults' => array(
                                'action' => 'onReport'
                            )
                        )
                    ),
                    'equations' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/equations',
                            'defaults' => array(
                                'action' => 'equations'
                            )
                        )
                    )
                )
            ),
            'benchmarkheadings' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/benchmarkheadings',
                    'defaults' => array(
                        'controller' => 'heading'
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id/:benchmarkGroup',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => null,
                                'benchmarkGroup' => null
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
                    ),
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
                            'route' => '/:benchmarkGroup',
                            'constraints' => array(
                                'benchmarkGroup' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'benchmarkGroup' => 0
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
                    ),
                )
            ),
            'reports' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/reports',
                    'defaults' => array(
                        'controller' => 'reports',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'compute' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/compute/:year',
                            'defaults' => array(
                                'action' => 'compute',
                                'year' => null
                            )
                        )
                    ),
                    'compute-one' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/compute-one/:observation[/:debug][/:benchmark]',
                            'defaults' => array(
                                'action' => 'computeOne',
                                'year' => null,
                                'observation' => 0,
                                'debug' => false,
                                'benchmark' => false
                            )
                        )
                    ),
                    'validate-one' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/validate-one/:observation[/:debug][/:benchmark]',
                            'defaults' => array(
                                'action' => 'validateOne',
                                'year' => null,
                                'observation' => 0,
                                'debug' => false,
                                'benchmark' => false
                            )
                        )
                    ),
                    'calculate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate[/year/:year]',
                            'defaults' => array(
                                'action' => 'calculate',
                                'year' => null
                            )
                        )
                    ),
                    'calculate-one' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate-one/:benchmark/:year[/:position]',
                            'defaults' => array(
                                'action' => 'calculateOne',
                                'year' => null,
                                'benchmark' => null,
                                'position' => null,
                                'forPercentileChange' => false
                            )
                        )
                    ),
                    'calculate-one-percent-change' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate-one-percent-change/:benchmark/:year[/:position]',
                            'defaults' => array(
                                'action' => 'calculateOne',
                                'year' => null,
                                'benchmark' => null,
                                'position' => null,
                                'forPercentChange' => true
                            )
                        )
                    ),
                    'calculate-one-system' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate-one-system/:system/:benchmark/:year[/:position]',
                            'defaults' => array(
                                'action' => 'calculateOneSystem',
                                'year' => null,
                                'system' => null,
                                'benchmark' => null,
                                'position' => null
                            )
                        )
                    ),
                    'calculateSystems' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculateSystems/year/:year',
                            'defaults' => array(
                                'action' => 'calculateSystems',
                                'year' => null
                            )
                        )
                    ),
                    'calculateOutliers' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculateOutliers[/year/:year]',
                            'defaults' => array(
                                'action' => 'calculateOutliers',
                                'year' => null
                            )
                        )
                    ),
                    'calculate-changes' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate-changes/:observation/:year[/:position]',
                            'defaults' => array(
                                'action' => 'calculateChanges',
                                'year' => null,
                                'observation' => null,
                                'position' => null
                            )
                        )
                    ),
                    'percent-changes' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/percent-changes[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'percentChanges',
                                'year' => null,
                                'format' => 'html'
                            )
                        )
                    ),
                    'percent-change' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/percent-change[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'national',
                                'year' => null,
                                'format' => 'html',
                                'forPercentChange' => true
                            )
                        )
                    ),
                    'calculate-outlier' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calculate-outlier/:benchmark/:year/:system[/:clear]',
                            'defaults' => array(
                                'action' => 'calculateOutlier',
                                'benchmark' => 0,
                                'clear' => false,
                                'year' => null,
                                'system' => 0
                            )
                        )
                    ),
                    'sendOutlier' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/send-outlier/:college/:year',
                            'defaults' => array(
                                'action' => 'sendOutlier',
                                'college' => null,
                                'year' => null
                            )
                        )
                    ),
                    // @deprecated:
                    'emailOutliers' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/emailOutliers[/:task]',
                            'defaults' => array(
                                'action' => 'emailOutliers',
                                'task' => 'send'
                            )
                        )
                    ),
                    'outlier' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/outlier',
                            'defaults' => array(
                                'action' => 'outlier'
                            )
                        )
                    ),
                    'national' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/national[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'national',
                                'year' => null,
                                'format' => 'html',
                                'system' => false,
                                'forPercentChange' => false
                            )
                        )
                    ),
                    'system' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/system[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'national',
                                'year' => null,
                                'format' => 'html',
                                'system' => true
                            )
                        )
                    ),
                    // Alias for system above
                    'network' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/network[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'national',
                                'year' => null,
                                'format' => 'html',
                                'system' => true
                            )
                        )
                    ),
                    // Non-credit in NCCBP
                    'non-credit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/non-credit[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'nonCredit',
                                'year' => null,
                                'format' => 'html',
                                'system' => true
                            )
                        )
                    ),
                    'social-mobility' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/social-mobility[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'national',
                                'year' => null,
                                'format' => 'html',
                                'system' => false,
                                'benchmarkGroupId' => 41
                            )
                        )
                    ),
                    'summary' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/summary[/:year]',
                            'defaults' => array(
                                'action' => 'summary',
                                'year' => null,
                                'print' => false
                            )
                        )
                    ),
                    'summary-print' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/summary-print[/:year]',
                            'defaults' => array(
                                'action' => 'summary',
                                'year' => null,
                                'print' => true
                            )
                        )
                    ),
                    'executive' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive[/:year]',
                            'defaults' => array(
                                'action' => 'executive',
                                'year' => null,
                            )
                        )
                    ),
                    'executive-print' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive-print[/:year]',
                            'defaults' => array(
                                'action' => 'executiveprint',
                                'year' => null,
                                'ipeds' => null,
                                'print' => true,
                                'open' => true
                            )
                        )
                    ),
                    'executive-print-admin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive-print-admin[/:ipeds[/:year]]',
                            'defaults' => array(
                                'action' => 'executiveprint',
                                'year' => null,
                                'ipeds' => null,
                                'print' => true,
                                'open' => true
                            )
                        )
                    ),
                    'executive-list' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive-list[/:year]',
                            'defaults' => array(
                                'action' => 'executiveList',

                            )
                        )
                    ),
                    'peer' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/peer',
                            'defaults' => array(
                                'action' => 'peer'
                            )
                        )
                    ),
                    'peer-demographic' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/peer-demographic',
                            'defaults' => array(
                                'action' => 'peerdemographic'
                            )
                        )
                    ),
                    'peer-colleges' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/peer-colleges/:year',
                            'defaults' => array(
                                'action' => 'peerColleges',
                                'year' => null
                            )
                        )
                    ),
                    'peer-benchmarks' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/peer-benchmarks/:year',
                            'defaults' => array(
                                'action' => 'peerBenchmarks',
                                'year' => null
                            )
                        )
                    ),
                    'delete-peer' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete-peer',
                            'defaults' => array(
                                'action' => 'deletePeerGroup'
                            )
                        )
                    ),
                    'peer-results' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/peer-results[/:format]',
                            'defaults' => array(
                                'action' => 'peerResults',
                                'format' => 'html'
                            )
                        )
                    ),
                    'explore' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/explore',
                            'defaults' => array(
                                'action' => 'explore',
                            )
                        )
                    ),
                    'best-performers' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/best-performers[/:year]',
                            'defaults' => array(
                                'action' => 'bestPerformers',
                                'year' => null
                            )
                        )
                    ),
                    'best-performers-result' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/best-performers-result/:year/:benchmark',
                            'defaults' => array(
                                'action' => 'bestPerformersResult',
                                'year' => null,
                                'benchmark' => null
                            )
                        )
                    ),
                    'strengths' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/strengths[/:year]',
                            'defaults' => array(
                                'action' => 'strengths',
                                'year' => null
                            )
                        )
                    ),
                    // The next routes are for Max's internal reports
                    'institutional' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/institutional',
                            'defaults' => array(
                                'action' => 'institutional'
                            )
                        )
                    ),
                    'all-institutional' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/all-institutional',
                            'defaults' => array(
                                'action' => 'allInstitutional'
                            )
                        )
                    ),
                    'instructional-costs' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/instructional-costs',
                            'defaults' => array(
                                'action' => 'institutionCosts'
                            )
                        )
                    ),
                    'division-summaries' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/division-summaries[/:year]',
                            'defaults' => array(
                                'action' => 'instructionalCosts',
                                'year' => null
                            )
                        )
                    ),
                    'activities-within-division' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/activities-within-division[/:year]',
                            'defaults' => array(
                                'action' => 'instructionalActivityCosts',
                                'year' => null
                            )
                        )
                    ),
                    'divisions-by-activity' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/divisions-by-activity[/:year]',
                            'defaults' => array(
                                'action' => 'unitCosts',
                                'year' => null
                            )
                        )
                    ),
                    'division-salaries' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/division-salaries[/:year]',
                            'defaults' => array(
                                'action' => 'unitDemographics',
                                'year' => null
                            )
                        )
                    ),
                    'student-services-costs' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/student-services-costs[/:year]',
                            'defaults' => array(
                                'action' => 'studentServicesCosts',
                                'year' => null
                            )
                        )
                    ),
                    'academic-support' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/academic-support[/:year]',
                            'defaults' => array(
                                'action' => 'academicSupport',
                                'year' => null
                            )
                        )
                    ),

                    // Custom reports
                    'custom' => array(
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/custom',
                            'defaults' => array(
                                'controller' => 'customReports',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'view' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/:id',
                                    'defaults' => array(
                                        'action' => 'view',
                                        'id' => 0,
                                        'print' => false
                                    )
                                )
                            ),
                            'public' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/public/:id[/:embed]',
                                    'defaults' => array(
                                        'action' => 'publicView',
                                        'id' => 0,
                                        'embed' => false
                                    )
                                )
                            ),
                            'print' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/print/:id',
                                    'defaults' => array(
                                        'action' => 'view',
                                        'id' => 0,
                                        'print' => true
                                    )
                                )
                            ),
                            'add' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/add',
                                    'defaults' => array(
                                        'action' => 'add'
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
                            'build' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/build/:id',
                                    'defaults' => array(
                                        'action' => 'build',
                                        'id' => 0
                                    )
                                )
                            ),
                            'copy' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/copy/:id',
                                    'defaults' => array(
                                        'action' => 'copy',
                                        'id' => 0
                                    )
                                )
                            ),
                            'publish' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/publish/:id',
                                    'defaults' => array(
                                        'action' => 'publish',
                                        'id' => 0
                                    )
                                )
                            ),
                            'duplicate' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/duplicate/:id',
                                    'defaults' => array(
                                        'action' => 'duplicate',
                                        'id' => 0
                                    )
                                )
                            ),
                            'delete' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/delete/:id',
                                    'defaults' => array(
                                        'action' => 'delete',
                                        'id' => 0
                                    )
                                )
                            ),
                            'addItem' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/:id/add[/:type]',
                                    'defaults' => array(
                                        'controller' => 'reportItems',
                                        'action' => 'add',
                                        'id' => 0,
                                        'type' => null
                                    )
                                )
                            ),
                            'reorderItems' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/:id/reorder',
                                    'defaults' => array(
                                        'controller' => 'reportItems',
                                        'action' => 'reorder',
                                        'id' => 0
                                    )
                                )
                            ),
                            'delete-item' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/:id/delete/:item_id',
                                    'defaults' => array(
                                        'controller' => 'reportItems',
                                        'action' => 'delete',
                                        'id' => 0,
                                        'item_id' => 0
                                    )
                                )
                            ),
                            'edit-item' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/:id/edit/:item_id',
                                    'defaults' => array(
                                        'controller' => 'reportItems',
                                        'action' => 'add',
                                        'id' => 0,
                                        'item_id' => 0
                                    )
                                )
                            ),
                            'clear-cache' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/clear-cache',
                                    'defaults' => array(
                                        'action' => 'clearCache',
                                    )
                                )
                            ),
                            'rebuild-cache' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/rebuild-cache/:id',
                                    'defaults' => array(
                                        'action' => 'rebuildCache',
                                        'id' => 0
                                    )
                                )
                            ),
                            'admin' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/admin',
                                    'defaults' => array(
                                        'action' => 'admin',
                                    )
                                )
                            ),
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
                            'route' => '/:id[/college/:college][/:redirect]',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0,
                                'college' => 0,
                                'redirect' => null
                            )
                        )
                    ),
                    'reset' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reset/:id',
                            'defaults' => array(
                                'action' => 'reset',
                                'id' => 0
                            )
                        )
                    ),
                    'queue' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/queue',
                            'defaults' => array(
                                'action' => 'approvalQueue',
                            )
                        )
                    ),
                    'impersonate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/impersonate/:id',
                            'defaults' => array(
                                'action' => 'impersonate',
                                'id' => 0
                            )
                        )
                    ),
                    'unimpersonate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/unimpersonate',
                            'defaults' => array(
                                'action' => 'unimpersonate'
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
                    ),
                    'exportLoginLinks' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/export-login-links',
                            'defaults' => array(
                                'action' => 'exportLoginLinks'
                            )
                        )
                    ),
                    'benchmarkorg' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/benchmarkorg/:org',
                            'defaults' => array(
                                'action' => 'benchmarkorg',
                                'org' => 'data-entry'
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
                    )
                )
            ),
            'institution' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/institution',
                    'defaults' => array(
                        'controller' => 'colleges',
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit[/:redirect]',
                            'defaults' => array(
                                'action' => 'edit',
                                'redirect' => null
                            )
                        )
                    ),
                    'users' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/users[/:redirect]',
                            'defaults' => array(
                                'action' => 'users',
                                'redirect' => null
                            )
                        )
                    ),
                    'subscribed' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/subscribed/:year',
                            'defaults' => array(
                                'controller' => 'subscription',
                                'action' => 'download',
                                'year' => null
                            )
                        )
                    ),
                    'search' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/search',
                            'defaults' => array(
                                'action' => 'search'
                            )
                        )
                    ),
                )
            ),
            'account' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account',
                    'defaults' => array(
                        'controller' => 'users',
                        'action' => 'account'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit[/:id][/:redirect]',
                            'defaults' => array(
                                'action' => 'accountedit',
                                'id' => null,
                                'redirect' => null
                            )
                        )
                    ),
                    'definitions' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/definitions',
                            'defaults' => array(
                                'action' => 'definitions'
                            )
                        )
                    ),
                    'settings' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/settings',
                            'defaults' => array(
                                'action' => 'accountSettings'
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
            'ipeds-institutions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/ipeds-institutions',
                    'defaults' => array(
                        'controller' => 'ipedsInstitutions',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'import' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import',
                            'defaults' => array(
                                'action' => 'import'
                            )
                        )
                    ),
                    'search' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/search',
                            'defaults' => array(
                                'action' => 'search',
                                'term' => null
                            )
                        )
                    )
                )
            ),
            'suppressions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/suppressions',
                    'defaults' => array(
                        'controller' => 'suppressions',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/[:subscription]',
                            'defaults' => array(
                                'action' => 'edit',
                            )
                        ),
                    )
                )
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
            'community' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/community',
                    'defaults' => array(
                        'controller' => 'index',
                        'action' => 'community'
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
            'export' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/export[/:action][/:year]',
                    'defaults' => array(
                        'controller' => 'export',
                        'action' => 'index',
                        'year' => null
                    )
                )
            ),
            'subscriptions' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/subscriptions[/:year]',
                    'defaults' => array(
                        'controller' => 'Admin',
                        'action' => 'dashboard',
                        'year' => null
                    )
                )
            ),
            'memberships' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/memberships',
                    'defaults' => array(
                        'controller' => 'subscription',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'invoice' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/invoice',
                            'defaults' => array(
                                'action' => 'sendinvoice'
                            )
                        )
                    ),
                    // Ajax handler to turn report access on/off (AAUP)
                    'report-access' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/report-access',
                            'defaults' => array(
                                'action' => 'reportAccess'
                            )
                        )
                    ),
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add/:college',
                            'defaults' => array(
                                'action' => 'adminAdd',
                                'college' => 0
                            )
                        )
                    )
                )
            ),
            'admin' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin',
                        'action' => 'dashboard',
                        'year' => null
                    )
                ),
                'child_routes' => array(
                    'memberships' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/memberships[/:year]',
                            'defaults' => array(
                                'action' => 'memberships',
                                'year' => null
                            )
                        )
                    ),
                    'memberships-edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/memberships/edit/:id',
                            'defaults' => array(
                                'action' => 'adminEdit',
                                'controller' => 'subscription',
                                'id' => null
                            )
                        )
                    ),
                    'changes' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/changes[/:college]',
                            'defaults' => array(
                                'action' => 'changes',
                                'college' => null
                            )
                        )
                    ),
                    'outliers' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/outliers[/:college_id]',
                            'defaults' => array(
                                'controller' => 'reports',
                                'college_id' => null,
                                'action' => 'adminOutliers'
                            )
                        )
                    ),
                    'check-migration' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/check-migration[/:minId]',
                            'defaults' => array(
                                'controller' => 'Admin',
                                'action' => 'checkMigration',
                                'minId' => 0
                            )
                        )
                    ),
                    'test-filter' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/test-filter',
                            'defaults' => array(
                                'controller' => 'Admin',
                                'action' => 'testFilter'
                            )
                        )
                    ),
                    'equations' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/equations',
                            'defaults' => array(
                                'controller' => 'Admin',
                                'action' => 'equations'
                            )
                        )
                    ),
                    'clean-up' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/clean-up',
                            'defaults' => array(
                                'action' => 'cleanUp',
                            )
                        )
                    ),
                    'settings' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/settings',
                            'defaults' => array(
                                'action' => 'settings',
                            )
                        )
                    ),
                )
            ),
            'tools' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/tools',
                    'defaults' => array(
                        'controller' => 'tool',
                        'action' => 'index'
                    )
                ),
                'child_routes' => array(
                    'exceldiff' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/exceldiff',
                            'defaults' => array(
                                'action' => 'exceldiff'
                            )
                        )
                    ),
                    'geocode' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/geocode',
                            'defaults' => array(
                                'action' => 'geocode'
                            )
                        )
                    ),
                    'nccbpAudit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/nccbpAudit',
                            'defaults' => array(
                                'action' => 'nccbpReportAudit'
                            )
                        )
                    ),
                    'calc-completion' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/calc-completion',
                            'defaults' => array(
                                'action' => 'calcCompletion'
                            )
                        )
                    ),
                    'all-gravatars' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/all-gravatars',
                            'defaults' => array(
                                'action' => 'allGravatars'
                            )
                        )
                    ),
                    'best' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/best',
                            'defaults' => array(
                                'action' => 'best'
                            )
                        )
                    ),
                    'copy-data' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/copy-data',
                            'defaults' => array(
                                'action' => 'copyData',
                            )
                        )
                    ),
                    'exec-addresses' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/exec-addresses',
                            'defaults' => array(
                                'action' => 'execAddresses'
                            )
                        )
                    ),
                    'separate' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/separate',
                            'defaults' => array(
                                'action' => 'separate'
                            )
                        )
                    ),
                    'info' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/info',
                            'defaults' => array(
                                'action' => 'info'
                            )
                        )
                    ),
                    'offsets' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/offsets[/:all]',
                            'defaults' => array(
                                'action' => 'offsets',
                                'all' => null
                            )
                        )
                    ),
                    'zeros' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/zeros[/:year][/:format]',
                            'defaults' => array(
                                'action' => 'zeros',
                                'year' => 0,
                                'format' => 'html'
                            )
                        )
                    ),
                    'fail' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/fail',
                            'defaults' => array(
                                'action' => 'fail',
                            )
                        )
                    ),
                    'repair-sequences' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/repair-sequences',
                            'defaults' => array(
                                'action' => 'repairSequences',
                            )
                        )
                    ),
                    'repair-report-sequences' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/repair-report-sequences',
                            'defaults' => array(
                                'action' => 'repairReportSequences',
                            )
                        )
                    ),
                    'equation-graph' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/equation-graph[/:benchmarkGroup]',
                            'defaults' => array(
                                'action' => 'equationGraph',
                                'benchmarkGroup' => null
                            )
                        )
                    ),
                    'lapsed' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/lapsed',
                            'defaults' => array(
                                'action' => 'lapsed',
                            )
                        )
                    ),
                    'copy-peer-groups' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/copy-peer-groups',
                            'defaults' => array(
                                'action' => 'copyPeerGroups',
                            )
                        )
                    ),
                    'suppressions' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/suppressions',
                            'defaults' => array(
                                'action' => 'suppressions',
                            )
                        )
                    ),
                    'download-suppressions' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/download-suppressions',
                            'defaults' => array(
                                'action' => 'downloadSuppressions',
                            )
                        )
                    ),
                    'audit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/audit[/:year]',
                            'defaults' => array(
                                'action' => 'audit',
                                'year' => null
                            )
                        )
                    ),
                    'audit-update' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/audit-update',
                            'defaults' => array(
                                'action' => 'auditUpdate',
                            )
                        )
                    ),
                    'merge-mcc' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/merge-mcc',
                            'defaults' => array(
                                'action' => 'mergeMCC',
                            )
                        )
                    ),
                    'email-test' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/email-test',
                            'defaults' => array(
                                'action' => 'emailTest',
                            )
                        )
                    ),
                    'ob-dat' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/ob-dat',
                            'defaults' => array(
                                'action' => 'observationDataMigration',
                            )
                        )
                    ),
                    'populate-modules' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/populate-modules',
                            'defaults' => array(
                                'action' => 'populateSections',
                            )
                        )
                    ),
                    'import-wf' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import-wf',
                            'defaults' => array(
                                'action' => 'importWf',
                            )
                        )
                    ),
                    'analyze-equation' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/analyze-equation',
                            'defaults' => array(
                                'action' => 'analyzeEquation',
                            )
                        )
                    ),
                    'import-data' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/import-data',
                            'defaults' => array(
                                'action' => 'importData',
                            )
                        )
                    ),
                    'upload-data' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/upload-data',
                            'defaults' => array(
                                'action' => 'uploadData',
                            )
                        )
                    ),
                    'log' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/log',
                            'defaults' => array(
                                'action' => 'log',
                            )
                        )
                    ),
                )
            ),
            'criteria' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/criteria',
                    'defaults' => array(
                        'controller' => 'criteria',
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
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete/:id',
                            'defaults' => array(
                                'action' => 'delete',
                                'id' => 0
                            )
                        )
                    ),
                    'reorder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reorder',
                            'defaults' => array(
                                'action' => 'reorder'
                            )
                        )
                    )
                )
            ),
            'peer-groups' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/peer-groups',
                    'defaults' => array(
                        'controller' => 'peerGroups',
                        'action' => 'index',
                    )
                ),
                'child_routes' => array(
                    'add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add',
                            'defaults' => array(
                                'action' => 'add',
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
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete/:id',
                            'defaults' => array(
                                'action' => 'delete',
                                'id' => 0
                            )
                        )
                    ),
                    'add-peer' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add-peer/:id',
                            'defaults' => array(
                                'action' => 'addPeer',
                                'id' => 0,
                            )
                        )
                    ),
                    'add-demographic' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add-demographic/:id',
                            'defaults' => array(
                                'action' => 'addDemographic',
                                'id' => 0,
                            )
                        )
                    ),
                    'delete-peer' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/delete-peer/:id/:peer',
                            'defaults' => array(
                                'action' => 'deletePeer',
                                'id' => 0,
                                'peer' => 0
                            )
                        )

                    )
                )
            ),
            'schedule-demo' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/schedule-demo',
                    'defaults' => array(
                        'controller' => 'PhlyContact\Controller\Contact',
                        'action' => 'index',
                        'subject' => 'Schedule Demo',
                        'body' => "Your institution:\n\n\nPreferred time and dates:\n\n\nWho will join the demo:"
                    )
                )
            ),
            // NCCBP
            'webinar' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/webinar',
                    'defaults' => array(
                        'controller' => 'PhlyContact\Controller\Contact',
                        'action' => 'index',
                        'subject' => 'Free Webinar',
                        'body' => "Your institution:\n\n\nWho will join the demo (please include name, title, and " .
                            "email for each person):\n\n\nWhich webinar would you like to join? \n" .
                            "Wednesday, Sept 2 at 2pm\n" .
                            "Thursday, Sept 3 at 10am\n" .
                            "Thursday, Sept 10 at 2pm\n" .
                            "Monday, Sept 14 at 2pm  \n"
                    )
                )
            ),
            // AAUP
            'consultation' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/consultation',
                    'defaults' => array(
                        'controller' => 'PhlyContact\Controller\Contact',
                        'action' => 'index',
                        'subject' => 'Free Consultation',
                        'body' => "I would like to schedule a free FCS consultation on (date and time)"
                    )
                )
            ),
            // Workforce
            'free-webinar' => array(
                'type' => 'Segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/free-webinar',
                    'defaults' => array(
                        'controller' => 'PhlyContact\Controller\Contact',
                        'action' => 'index',
                        'subject' => 'Free Webinar',
                        'body' => "Your institution:\n\n\nWho will join the demo (please include name, title, and " .
                            "email for each person):\n\n\nWhich webinar would you like to join? \n" .
                            "Wednesday, Sept 9 at 10 am\n" .
                            "Tuesday, Sept 15 at 2pm\n"
                    )
                )
            ),
            'all-colleges.json' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/files/all-colleges.json',
                    'defaults' => array(
                        'controller' => 'colleges',
                        'action' => 'cacheColleges'
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
            /*'members' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/members',
                    'defaults' => array(
                        'controller' => 'pages',
                        'action' => 'view'
                    )
                )
            ),*/
            'reset-password' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/reset-password',
                    'defaults' => array(
                        'controller' => 'goalioforgotpassword_forgot',
                        'action' => 'forgot'
                    )
                )
            ),
            'chat-login' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/chat-login',
                    'defaults' => array(
                        'controller' => 'users',
                        'action' => 'chatLogin'
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
                        'route' => 'import <type> <year>',
                        'defaults' => array(
                            'controller' => 'import',
                            'action' => 'background',
                            'year' => null
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
            'tool' => 'Mrss\Controller\ToolController',
            'import' => 'Mrss\Controller\ImportController',
            'export' => 'Mrss\Controller\ExportController',
            'colleges' => 'Mrss\Controller\CollegeController',
            'ipedsInstitutions' => 'Mrss\Controller\IpedsInstitutionController',
            'systems' => 'Mrss\Controller\SystemController',
            'structures' => 'Mrss\Controller\StructureController',
            'observations' => 'Mrss\Controller\ObservationController',
            'subobservations' => 'Mrss\Controller\SubObservationController',
            'benchmarks' => 'Mrss\Controller\BenchmarkController',
            'heading' => 'Mrss\Controller\HeadingController',
            'benchmarkgroups' => 'Mrss\Controller\BenchmarkGroupController',
            'subscription' => 'Mrss\Controller\SubscriptionController',
            'studies' => 'Mrss\Controller\StudyController',
            'sections' => 'Mrss\Controller\SectionController',
            'offercodes' => 'Mrss\Controller\OfferCodeController',
            'settings' => 'Mrss\Controller\SettingController',
            'pages' => 'Mrss\Controller\PageController',
            'reports' => 'Mrss\Controller\ReportController',
            'customReports' => 'Mrss\Controller\CustomReportController',
            'peerGroups' => 'Mrss\Controller\PeerGroupController',
            'criteria' => 'Mrss\Controller\CriterionController',
            'reportItems' => 'Mrss\Controller\ReportItemController',
            'users' => 'Mrss\Controller\UserController',
            'issues' => 'Mrss\Controller\IssueController',
            'suppressions' => '\Mrss\Controller\SuppressionController',
            'EquationValidator' => '\Mrss\Validator\Equation',
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
    /*'view_manager' => array(
        'template_path_stack' => array(
            'mrss' => __DIR__ . '/../view',
            'zfc-user' => __DIR__ . '/../view',
        ),
    ),*/
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
            'error/403' => __DIR__ . '/../view/error/403.phtml',
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
        'configuration' => array(
          'orm_default' => array(
              //'metadata_cache' => 'my_memcache',
              'query_cache' => 'filesystem'
          )
        ),
        'sql_logger_collector' => array(
            'orm_default' => array()
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
