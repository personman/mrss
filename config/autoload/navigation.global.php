<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Home',
                'uri' => '/',
                'controller' => 'index',
                'action' => 'index',
                'route' => 'general'
            ),
            array(
                'label' => 'Subscribe',
                'route' => 'subscribe'
            ),
            array(
                'label' => 'Studies',
                'controller' => 'studies',
                'route' => 'studies'
            ),
            array(
                'label' => 'Imports',
                'controller' => 'import',
                'action' => 'index',
                'route' => 'general'
            ),
            array(
                'label' => 'Colleges',
                'uri' => '/colleges',
                'controller' => 'colleges',
                'action' => 'index',
                'route' => 'general'
            ),
            array(
                'label' => 'Benchmarks',
                'uri' => '/benchmarks',
                'controller' => 'benchmarks',
                'action' => 'index',
                'route' => 'general'
            ),
            array(
                'label' => 'Pages',
                'route' => 'pages',
                'controller' => 'pages',
                'action' => 'index'
            ),
            array(
                'label' => 'User',
                'route' => 'zfcuser',
                'controller' => 'zfcuser',
                'action' => 'index'
            )
        )
    )
);
