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

// Note: This navigation setup is modified by module/Mrss/src/Mrss/Service/
// NavigationFactory.php
return array(
    'navigation' => array(
        'default' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home'
            ),
            'about' => array(
                'label' => 'The Project',
                'uri' => '#',
                'pages' => array(
                    array(
                        'label' => 'Project Overview',
                        'uri' => '/about'
                    ),
                    'benchmarks' => array(
                        'label' => 'Benchmarks',
                        'uri' => '/Benchmarks',
                    ),
                    'partners' => array(
                        'label' => 'Project Partners',
                        'uri' => '/partners'
                    ),
                    'nccet' => array(
                        'label' => 'NCCET',
                        'uri' => '/Collaboration-National-Council-for-Continuing-Education-and-Training-NCCET'
                    ),
                    'timeline' => array(
                        'label' => 'Timeline',
                        'uri' => 'timeline'
                    ),
                    /*array(
                        'label' => 'Testimonials',
                        'uri' => '/testimonials'
                    ),*/
                )
            ),
            'about2' => array(
                'uri' => '#',
                'label' => 'About Us',
                'pages' => array(
                    array(
                        'label' => 'The Benchmarking Institute',
                        'uri' => '/National-Higher-Education-Benchmarking-Institute'
                    ),
                    array(
                        'label' => 'Our Staff',
                        'uri' => '/staff'
                    ),
                    array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    )

                ),
            ),
            'data-entry' => array(
                'label' => 'Data Entry',
                'route' => 'data-entry',
                'pages' => array(

                )
            ),
            'reports' => array(
                'label' => 'Reports',
                'uri' => '/reports',
                'pages' => array(
                    array(
                        'label' => 'National Report',
                        'uri' => '/reports/national'
                    ),
                    array(
                        'label' => 'Summary Report',
                        'uri' => '/reports/summary'
                    ),
                    array(
                        'label' => 'Peer Comparison',
                        'uri' => '/reports/peer'
                    )
                )
            ),
            'subscribe' => array(
                'label' => 'Join',
                'route' => 'subscribe',
                'controller' => 'subscription',
                'pages' => array(
                    'subscribe' => array(
                        'label' => 'How to Join',
                        'uri' => '/subscription'
                    ),
                    'membership-benefits' => array(
                        'label' => 'Membership Benefits',
                        'uri' => '/Membership-Benefits'
                    ),
                    'confidentiality' => array(
                        'label' => 'Confidentiality',
                        'uri' => 'confidentiality'
                    )
                )
            ),
            'help' => array(
                'label' => 'Help',
                'uri' => '#',
                'pages' => array(
                    'glossary' => array(
                        'label' => 'Glossary',
                        'uri' => '/glossary'
                    ),
                    'faq' => array(
                        'label' => 'FAQ',
                        'uri' => '/faq'
                    ),
                    'contact' => array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    ),
                )
            ),
            'contact' => array(
                'label' => 'Contact Us',
                'uri' => '/contact'
            ),
            'account' => array(
                'label' => 'Your Account',
                'route' => 'account'
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
            'reports' => array(
                'label' => 'Reports',
                'route' => 'reports/calculate'
            ),
            array(
                'label' => 'Import',
                'route' => 'import'
            ),
            array(
                'label' => 'Export',
                'route' => 'export'
            ),
            array(
                'label' => 'Colleges',
                'controller' => 'colleges',
                'action' => 'index',
                'route' => 'colleges'
            ),
            array(
                'label' => 'Systems',
                'route' => 'systems'
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
