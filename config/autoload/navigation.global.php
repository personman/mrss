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
            'about2' => array(
                'uri' => '#',
                'label' => 'About Us',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Project Overview',
                        'uri' => '/about'
                    ),
                    'partners' => array(
                        'label' => 'Project Partners',
                        'uri' => '/partners'
                    ),
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
            'about' => array(
                'label' => 'Benchmarks',
                'uri' => '/Benchmarks',
                'pages' => array(
                    'benchmarks' => array(
                        'label' => 'Benchmarks',
                        'uri' => '/Benchmarks',
                    ),
                    'nccet' => array(
                        'label' => 'NCCET',
                        'uri' => '/Collaboration-National-Council-for-Continuing-Education-and-Training-NCCET'
                    ),
                    /*array(
                        'label' => 'Testimonials',
                        'uri' => '/testimonials'
                    ),*/
                )
            ),
            'renew' => getRenewMenu(),
            'data-entry' => getDataMenu(),
            'reports' => getReportMenu(),
            'reports_preview' => array(
                'label' => 'Reports',
                'uri' => '/Membership-Benefits'
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
                        'uri' => '/confidentiality'
                    ),
                    'timeline' => array(
                        'label' => 'Timeline',
                        'uri' => '/timeline'
                    ),
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
            'account' => getAccountMenu(),
            'login' => array(
                'label' => 'Sign In',
                'route' => 'zfcuser/login',
                'class' => 'headerLoginLink'
            ),
        ),
        'nccbp' => array(
            'home' => array(
                'label' => 'Home',
                'route' => 'home'
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'uri' => '/benchmarks'
            ),

            /*'nccbp' => array(
                'label' => 'NCCBP',
                'uri' => '#',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Overview',
                        'uri' => '/overview'
                    ),
                    'benchmarks' => array(
                        'label' => 'Benchmarks',
                        'uri' => '/benchmarks'
                    )
                )
            ),*/
            'who-we-help' => array(
                'label' => 'Who We Help',
                'uri' => '#',
                'pages' => array(
                    'map' => array(
                        'label' => 'Peer Institutions',
                        'uri' => '/peers'
                    ),
                    'researchers' => array(
                        'label' => 'Researchers',
                        'uri' => '/researchers'
                    ),
                    'executives' => array(
                        'label' => 'Executive Leadership',
                        'uri' => '/executive-leadership'
                    ),
                    'systems' => array(
                        'label' => 'Systems',
                        'uri' => '/college-systems'
                    )
                )
            ),
            'reports-overview' => array(
                'label' => 'Reports (Overview)',
                'uri' => '#',
                'pages' => array(
                    'national-report' => array(
                        'label' => 'National and System Reports',
                        'uri' => '/national-report'
                    ),
                    'executive-report' => array(
                        'label' => 'Executive Report',
                        'uri' => '/executive-report'
                    ),
                    'trend' => array(
                        'label' => 'Trend Report',
                        'uri' => '/trend-report'
                    ),
                    'peer-report' => array(
                        'label' => 'Peer Comparisons',
                        'uri' => '/peer-report'
                    ),
                    'best-performers-report' => array(
                        'label' => 'Best Performers',
                        'uri' => '/best-performers-report'
                    ),
                    'schedule-demo' => array(
                        'label' => 'Schedule Demo',
                        'uri' => '/schedule-demo'
                    ),
                )
            ),
            'renew' => getRenewMenu(),
            'data-entry' => getDataMenu(),
            'reports' => getReportMenu(),
            'join' => array(
                'label' => 'Join Now',
                'uri' => '#',
                'pages' => array(
                    'join-form' => array(
                        'label' => 'Join Form',
                        'uri' => '/join'
                    ),
                    'benefits' => array(
                        'label' => 'Benefits',
                        'uri' => '/benefits'
                    ),
                    'qa-process' => array(
                        'label' => 'QA Process',
                        'uri' => 'qa-process'
                    ),
                    'timeline' => array(
                        'label' => 'Timeline',
                        'uri' => '/timeline'
                    ),
                    'schedule-demo' => array(
                        'label' => 'Schedule Demo',
                        'uri' => '/schedule-demo'
                    ),
                )
            ),
            'about' => array(
                'label' => 'About Us',
                'uri' => '#',
                'pages' => array(
                    'benchmarking-institute' => array(
                        'label' => 'Benchmarking Institute',
                        'uri' => '/benchmarking-institute'
                    ),
                    'projects' => array(
                        'label' => 'Other Projects',
                        'uri' => '/projects'
                    ),
                    'staff' => array(
                        'label' => 'Our Staff',
                        'uri' => '/staff'
                    ),
                    'contact' => array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    )

                )
            ),
            'data-documentation' => array(
                'label' => 'Data Documentation',
                'uri' => '#',
                'pages' => array(
                    'faq' => array(
                        'label' => 'Submitted Values',
                        'uri' => '/submitted-values'
                    ),
                    'contact' => array(
                        'label' => 'Data Dictionary',
                        'uri' => '/data-dictionary'
                    ),
                    'calculations' => array(
                        'label' => 'Benchmark Calculations',
                        'uri' => '/calculations'
                    ),
                    'changes' => array(
                        'label' => 'Changes and Errata',
                        'uri' => '/changes'
                    ),
                )
            ),
            'help' => array(
                'label' => 'Help',
                'uri' => '#',
                'pages' => array(
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
                'uri' => '/contact',
                'pages' => array(
                    'schedule-demo' => array(
                        'label' => 'Schedule Demo',
                        'uri' => '/schedule-demo'
                    ),
                )
            ),
            'account' => getAccountMenu(),
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
            'reports/explore' => array(
                'label' => 'Explore Data',
                'route' => 'reports/explore'
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
                'label' => 'Tools',
                'route' => 'tools',
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

function getReportMenu()
{
    return array(
        'label' => 'Reports',
        'uri' => '/reports',
        'pages' => array(
            'outlier' => array(
                'label' => 'Outlier Report',
                'uri' => '/reports/outlier'
            ),
            'national' => array(
                'label' => 'National Report',
                'uri' => '/reports/national'
            ),
            'system' => array(
                'label' => 'System Report',
                'uri' => '/reports/system'
            ),
            'summary' => array(
                'label' => 'Summary Report',
                'uri' => '/reports/summary'
            ),
            'peer' => array(
                'label' => 'Peer Comparison',
                'uri' => '/reports/peer'
            )
        )
    );
}

function getAccountMenu()
{
    return array(
        'label' => 'Your Account',
        'route' => 'account',
        'pages' => array(
            'home' => array(
                'label' => 'Member Home',
                'uri' => '/members'
            ),
            'account' => array(
                'label' => 'Manage Your Account',
                'route' => 'account',
            ),
            'institution' => array(
                'label' => 'Manage Your Institution',
                'route' => 'institution/edit'
            ),
            'users' => array(
                'label' => 'Manage Your Institution\'s Users',
                'route' => 'institution/users'
            ),
            'logout' => array(
                'label' => 'Sign Out',
                'route' => 'zfcuser/logout',
            )
        )
    );
}

function getDataMenu()
{
    return array(
        'label' => 'Data Entry',
        'route' => 'data-entry',
        'pages' => array(

        )
    );
}

function getRenewMenu()
{
    return array(
        'label' => 'Renew',
        'route' => 'renew',
        'class' => 'renew-nav'
    );
}
