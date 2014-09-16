<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'bjyauthorize' => array(
        // default role for unauthenticated users
        'default_role'          => 'guest',

        // default role for authenticated users (if using the
        // 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider' identity provider)
        'authenticated_role'    => 'user',

        // identity provider service name
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',

        // Role providers to be used to load all available roles into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'Mrss\Entity\Role',
            ),
        ),

        // Resource providers to be used to load all available resources into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'resource_providers'    => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'adminMenu' => array(),
                'system_admin' => array(),
            ),
        ),

        // Rule providers to be used to load all available rules into Zend\Permissions\Acl\Acl
        // Keys are the provider service names, values are the options to be passed to the provider
        'rule_providers'        => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    // allow guests and users (and admins, through inheritance)
                    // the "wear" privilege on the resource "pants"
                    array(array('admin'), 'adminMenu', 'view')
                ),
            ),
        ),

        // Guard listeners to be attached to the application event manager
        'guards'                => array(
            'BjyAuthorize\Guard\Controller' => array(
                // Guests can see the index and user controller
                array(
                    'controller' => 'index',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'PhlyContact\Controller\Contact',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'subscription',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'zfcuser',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'goalioforgotpassword_forgot',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'pages',
                    'action' => 'view',
                    'roles' => array('guest')
                ),
                array(
                    'controller' => 'ipedsInstitutions',
                    'action' => 'search',
                    'roles' => array('guest')
                ),
                // Only authenticated users can look at these:
                array(
                    'controller' => 'observations',
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'subobservations',
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'reports',
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'users',
                    'action' => array('account', 'accountedit'),
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'zfcuserimpersonate_adminController',
                    'action' => 'unimpersonateUser',
                    'roles' => array('user', 'admin')
                ),
                array(
                    'controller' => 'subscription',
                    'action' => 'renew',
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'colleges',
                    'action' => 'edit',
                    'roles' => array('user')
                ),
                array(
                    'controller' => 'colleges',
                    'action' => 'users',
                    'roles' => array('user')
                ),
                // System admin
                array(
                    'controller' => 'observations',
                    'action' => 'systemadminoverview',
                    'roles' => array('system_admin')
                ),
                array(
                    'controller' => 'observations',
                    'action' => 'systemimport',
                    'roles' => array('system_admin')
                ),
                array(
                    'controller' => 'observations',
                    'action' => 'systemexport',
                    'roles' => array('system_admin')
                ),
                array(
                    'controller' => 'observations',
                    'action' => 'switch',
                    'roles' => array('system_admin')
                ),
                // Admin stuff
                array(
                    'controller' => 'pages',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'Admin',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'tool',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'benchmarks',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'studies',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'offercodes',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'import',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'export',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'systems',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'settings',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'benchmarkgroups',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'DoctrineORMModule\Yuml\YumlController',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'colleges',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'users',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'ipedsInstitutions',
                    'action' => 'import',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'zfcuserimpersonate_adminController',
                    'action' => 'impersonateUser',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'subscription',
                    'action' => 'delete',
                    'roles' => array('admin')
                ),
                array(
                    'controller' => 'reports',
                    'action' => 'emailOutliers',
                    'roles' => array('admin')
                ),
                // Since the background action is fired by console, lift guard
                array(
                    'controller' => 'import',
                    'action' => 'background',
                    'roles' => array('guest')
                ),
            )
        ),

        // strategy service name for the strategy listener to be used when permission-related errors are detected
        'unauthorized_strategy' => 'BjyAuthorize\View\UnauthorizedStrategy',

        // Template name for the unauthorized strategy
        'template'              => 'error/403',
    ),

    'service_manager' => array(
        'factories' => array(
            'BjyAuthorize\Config'                   => 'BjyAuthorize\Service\ConfigServiceFactory',
            'BjyAuthorize\Guards'                   => 'BjyAuthorize\Service\GuardsServiceFactory',
            'BjyAuthorize\RoleProviders'            => 'BjyAuthorize\Service\RoleProvidersServiceFactory',
            'BjyAuthorize\ResourceProviders'        => 'BjyAuthorize\Service\ResourceProvidersServiceFactory',
            'BjyAuthorize\RuleProviders'            => 'BjyAuthorize\Service\RuleProvidersServiceFactory',
            'BjyAuthorize\Guard\Controller'         => 'BjyAuthorize\Service\ControllerGuardServiceFactory',
            'BjyAuthorize\Guard\Route'              => 'BjyAuthorize\Service\RouteGuardServiceFactory',
            'BjyAuthorize\Provider\Role\Config'     => 'BjyAuthorize\Service\ConfigRoleProviderServiceFactory',
            'BjyAuthorize\Provider\Role\ZendDb'     => 'BjyAuthorize\Service\ZendDbRoleProviderServiceFactory',
            'BjyAuthorize\Provider\Resource\Config' => 'BjyAuthorize\Service\ConfigResourceProviderServiceFactory',
            'BjyAuthorize\Service\Authorize'        => 'BjyAuthorize\Service\AuthorizeFactory',
            'BjyAuthorize\Provider\Identity\ProviderInterface'
                => 'BjyAuthorize\Service\IdentityProviderServiceFactory',
            'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider'
                => 'BjyAuthorize\Service\AuthenticationIdentityProviderServiceFactory',
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider'
                => 'BjyAuthorize\Service\ObjectRepositoryRoleProviderFactory',
            'BjyAuthorize\Collector\RoleCollector'  => 'BjyAuthorize\Service\RoleCollectorServiceFactory',
            'BjyAuthorize\Provider\Identity\ZfcUserZendDb'
                => 'BjyAuthorize\Service\ZfcUserZendDbIdentityProviderServiceFactory',
            'BjyAuthorize\View\UnauthorizedStrategy'
                => 'BjyAuthorize\Service\UnauthorizedStrategyServiceFactory',
        ),
        'invokables'  => array(
            'BjyAuthorize\View\RedirectionStrategy' => 'BjyAuthorize\View\RedirectionStrategy',
        ),
        'aliases'     => array(
            'bjyauthorize_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
        'initializers' => array(
            'BjyAuthorize\Service\AuthorizeAwareServiceInitializer'
                => 'BjyAuthorize\Service\AuthorizeAwareServiceInitializer'
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            // Set in module config
            //'error/403' => __DIR__ . '/../../'
            //    . 'vendor/bjyoungblood/bjy-authorize/view/error/403.phtml',
            'zend-developer-tools/toolbar/bjy-authorize-role'
                => __DIR__ . '/../../vendor/bjyoungblood/bjy-authorize/view/'
                . 'zend-developer-tools/toolbar/bjy-authorize-role.phtml',
        ),
    ),

    'zenddevelopertools' => array(
        'profiler' => array(
            'collectors' => array(
                'bjy_authorize_role_collector' => 'BjyAuthorize\\Collector\\RoleCollector',
            ),
        ),
        'toolbar' => array(
            'entries' => array(
                'bjy_authorize_role_collector' => 'zend-developer-tools/toolbar/bjy-authorize-role',
            ),
        ),
    ),
);
