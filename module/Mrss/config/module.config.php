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

            'submitted-values' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/submitted-values[/:year]',
                    'defaults' => array(
                        'controller' => 'observations',
                        'action' => 'submittedValues',
                        'year' => null
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
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/:benchmarkGroup',
                            'constraints' => array(
                                'benchmarkGroup' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'action' => 'dataEntry',
                                'benchmarkGroup' => 0
                            )
                        ),
                        'child_routes' => array(
                            'subob' => array(
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
                            ),
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
                    )
                )
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
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
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
            'benchmark' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/benchmark[/study/:study]',
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
                    'reorder' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reorder',
                            'defaults' => array(
                                'action' => 'reorder'
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
                                'system' => false
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
                    'summary' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/summary[/:year]',
                            'defaults' => array(
                                'action' => 'summary',
                                'year' => null
                            )
                        )
                    ),
                    'executive' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive[/:ipeds[/:year]]',
                            'defaults' => array(
                                'action' => 'executive',
                                'year' => null,
                                'ipeds' => null
                            )
                        )
                    ),
                    'executive-print' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/executive-print[/:ipeds[/:year]]',
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
                    'trend' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/trend',
                            'defaults' => array(
                                'action' => 'trend'
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
                            'route' => '/:id[/college/:college][/:redirect]',
                            'defaults' => array(
                                'action' => 'edit',
                                'id' => 0,
                                'college' => 0,
                                'redirect' => null
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
                    'changes' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/changes',
                            'defaults' => array(
                                'action' => 'changes'
                            )
                        )
                    ),
                    'outliers' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/outliers',
                            'defaults' => array(
                                'controller' => 'reports',
                                'action' => 'adminOutliers'
                            )
                        )
                    )
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
                    'best' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/best',
                            'defaults' => array(
                                'action' => 'best'
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
            'members' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/members',
                    'defaults' => array(
                        'controller' => 'pages',
                        'action' => 'view'
                    )
                )
            ),
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
            'observations' => 'Mrss\Controller\ObservationController',
            'subobservations' => 'Mrss\Controller\SubObservationController',
            'benchmarks' => 'Mrss\Controller\BenchmarkController',
            'benchmarkgroups' => 'Mrss\Controller\BenchmarkGroupController',
            'subscription' => 'Mrss\Controller\SubscriptionController',
            'studies' => 'Mrss\Controller\StudyController',
            'offercodes' => 'Mrss\Controller\OfferCodeController',
            'settings' => 'Mrss\Controller\SettingController',
            'pages' => 'Mrss\Controller\PageController',
            'reports' => 'Mrss\Controller\ReportController',
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
    /*'view_manager' => array(
        'template_path_stack' => array(
            'mrss' => __DIR__ . '/../view'
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
              //'query_cache' => 'filesystem'
          )
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
