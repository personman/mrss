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
            'home' => array(
                'label' => 'Home',
                'route' => 'home'
            ),
            'about' => array(
                'label' => 'About Us',
                'uri' => '#',
                'pages' => array(
                    array(
                        'label' => 'The Project',
                        'uri' => '/about'
                    ),
                    array(
                        'label' => 'The Benchmarking Institute',
                        'uri' => '/National-Higher-Education-Benchmarking-Institute'
                    ),
                    'nccet' => array(
                        'label' => 'NCCET',
                        'uri' => '/Collaboration-National-Council-for-Continuing-Education-and-Training-NCCET'
                    ),
                    'partners' => array(
                        'label' => 'Project Partners',
                        'uri' => '/partners'
                    ),
                    array(
                        'label' => 'Our Staff',
                        'uri' => '/staff'
                    ),
                    /*array(
                        'label' => 'Testimonials',
                        'uri' => '/testimonials'
                    ),*/
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
            'reports' => array(
                'label' => 'Reports',
                'uri' => '/reports'
            ),
            /*
            // ToDo: before activating below code make sure that Membership Benefits don't show when logged in. PRA 130626
            array(
              'label' => 'Subscribe',
              'uri' => '#',
              'pages' => array(
                array(
                   'label' => 'Subscribe Now',
                   'route' => 'subscribe',
                   'controller' => 'subscription'
                 ), 
                array(
                   'label' => 'Membership Benefits',
                   'uri' => '/Membership-Benefits'
                 ),
               )
            ),
            */
            'subscribe' => array(
                'label' => 'Subscribe',
                'route' => 'subscribe',
                'controller' => 'subscription',
                'pages' => array(
                    'subscribe' => array(
                        'label' => 'Subscribe Now',
                        'uri' => '/subscription'
                    ),
                    'membership-benefits' => array(
                        'label' => 'Membership Benefits',
                        'uri' => '/Membership-Benefits'
                    ),
                    'timeline' => array(
                        'label' => 'Timeline',
                        'uri' => 'timeline'
                    ),
                    'confidentiality' => array(
                        'label' => 'Confidentiality',
                        'uri' => 'confidentiality'
                    )
                )
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'uri' => '/Benchmarks',
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
