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
                'route' => 'home'
            ),
            array(
                'label' => 'About Us',
                'uri' => '#',
                'pages' => array(
                    /*array(
                        'label' => 'About MRSS',
                        'uri' => '/mrss'
                    ),*/
                    array(
                        'label' => 'Our Staff',
                        'uri' => '/staff'
                    ),
                    array(
                        'label' => 'Testimonials',
                        'uri' => '/testimonials'
                    ),
                    array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    )
                )
            ),
            'data-entry' => array(
                'label' => 'Data Entry',
                'route' => 'data-entry',
                'pages' => array(

                )
            ),
            array(
                'label' => 'Help',
                'uri' => '#',
                'pages' => array(
                    array(
                        'label' => 'Glossary',
                        'uri' => '/glossary'
                    )
                )
            ),
            'subscribe' => array(
                'label' => 'Subscribe',
                'route' => 'subscribe',
                'controller' => 'subscription'
            ),
            array(
                'label' => 'Contact Us',
                'uri' => '/contact'
            ),
            'login' => array(
                'label' => 'Log In',
                'route' => 'zfcuser/login'
            ),
            'logout' => array(
                'label' => 'Log Out',
                'route' => 'zfcuser/logout',
            )
        ),
        'admin' => array(
            'dashboard' => array(
                'label' => 'Dashboard',
                'route' => 'admin'
            ),
            'studies' => array(
                'label' => 'Studies',
                'controller' => 'studies',
                'route' => 'studies'
            ),
            array(
                'label' => 'Imports',
                'route' => 'import'
            ),
            array(
                'label' => 'Colleges',
                'controller' => 'colleges',
                'action' => 'index',
                'route' => 'colleges'
            ),
            array(
                'label' => 'Benchmarks',
                'route' => 'benchmarks'
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
                'action' => 'index',
                'ulClass' => 'dropdown-menu',
            ),
        )
    )
);
