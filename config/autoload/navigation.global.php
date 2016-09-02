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
                    /*'nccet' => array(
                        'label' => 'NCCET',
                        'uri' => '/Collaboration-National-Council-for-Continuing-Education-and-Training-NCCET'
                    ),*/
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
                'uri' => '/overview',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Overview',
                        'uri' => '/overview',
                    ),
                    'research' => array(
                        'label' => 'Research',
                        'uri' => '/research',
                    ),
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
                        'uri' => '/confidentiality'
                    ),
                    'timeline' => array(
                        'label' => 'Timeline',
                        'uri' => '/timeline'
                    ),
                    'schedule-demo' => array(
                        'label' => 'Schedule a Demo',
                        'uri' => '/schedule-demo'
                    ),
                )
            ),
            /*'help' => array(
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
            ),*/
            'data-documentation' => array(
                'label' => 'Data Documentation',
                'uri' => '#',
                'pages' => array(
                    'submitted-values' => array(
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
                    'faq' => array(
                        'label' => 'FAQ',
                        'uri' => '/faq'
                    ),                )
            ),
            'schedule-demo' => array(
                'label' => 'Schedule a Demo',
                'uri' => '/schedule-demo'
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
            'admin' => array(
                'label' => '<span class="glyphicon glyphicon-cog icon-cog icon-white adminMenuIcon"></span>',
                'uri' => '/admin',
                'resource' => 'adminMenu',
                'privilege' => 'view',
                'pages' => getAdminMenu()
            )
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
                    ),
                    'testimonials' => array(
                        'label' => 'Testimonials',
                        'uri' => '/testimonials'
                    )
                )
            ),
            'reports-overview' => array(
                'label' => 'Reports',
                'uri' => '#',
                'pages' => array(
                    'overview' => array(
                        'label' => 'Overview',
                        'uri' => '/reports-overview'
                    ),
                    'national-report' => array(
                        'label' => 'National and System Reports',
                        'uri' => '/national-report'
                    ),
                    'executive-report' => array(
                        'label' => 'Executive Report',
                        'uri' => '/executive-report'
                    ),
                    /*'trend' => array(
                        'label' => 'Trend Report',
                        'uri' => '/trend-report'
                    ),*/
                    'peer-report' => array(
                        'label' => 'Peer Comparisons',
                        'uri' => '/peer-report'
                    ),
                    'custom-report' => array(
                        'label' => 'Custom Reports',
                        'uri' => '/custom-report'
                    ),
                    /*'best-performers-report' => array(
                        'label' => 'Best Performers',
                        'uri' => '/best-performers-report'
                    ),*/
                    'case-studies' => array(
                        'label' => 'Case Studies',
                        'uri' => '/case-studies'
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
                'class' => 'join-nav',
                'pages' => array(
                    'how-to-join' => array(
                        'label' => 'How to Join',
                        'uri' => '/how-to-join'
                    ),
                    'benefits' => array(
                        'label' => 'Benefits',
                        'uri' => '/benefits'
                    ),
                    /*'qa-process' => array(
                        'label' => 'QA Process',
                        'uri' => 'qa-process'
                    ),*/
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
                    /*'faq' => array(
                        'label' => 'FAQ',
                        'uri' => '/faq'
                    ),*/
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
                    'contact' => array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    ),
                )
            ),
            'account' => getAccountMenu(),
            'admin' => array(
                'label' => '<span class="glyphicon glyphicon-cog icon icon-cog adminMenuIcon"></span>',
                'uri' => '/admin',
                'resource' => 'adminMenu',
                'privilege' => 'view',
                'pages' => getAdminMenu()
            )
        ),
        'aaup' => array(
            // Public navigation
            'about' => array(
                'label' => 'About',
                'uri' => '#',
                'pages' => array(
                    'aaup' => array(
                        'label' => 'AAUP',
                        'uri' => '/aaup'
                    ),
                    'history' => array(
                        'label' => 'FCS History',
                        'uri' => '/history'
                    ),
                    'survey' => array(
                        'label' => 'Survey',
                        'uri' => '/survey'
                    ),
                    'research' => array(
                        'label' => 'Research',
                        'uri' => '/research'
                    ),
                )
            ),
            'start' => array(
                'label' => 'Start',
                'uri' => '/participate',
                'class' => 'renew-nav'
            ),
            'results' => array(
                'label' => 'Results',
                'uri' => '#',
                'pages' => array(
                    'sample-results' => array(
                        'label' => 'Sample Results',
                        'uri' => '/sample-results'
                    ),
                    'order' => array(
                        'label' => 'Order',
                        'uri' => '/order'
                    ),
                )
            ),
            'resources' => array(
                'label' => 'Resources',
                'uri' => '#',
                'pages' => array(
                    'presentations' => array(
                        'label' => 'Presentations',
                        'uri' => '/presentations'
                    ),
                    'publications' => array(
                        'label' => 'Publications',
                        'uri' => '/publications'
                    ),
                    'webinars' => array(
                        'label' => 'Webinars',
                        'uri' => '/webinars'
                    ),
                    'media' => array(
                        'label' => 'Media',
                        'uri' => '/media'
                    ),
                )
            ),
            'contact' => array(
                'label' => 'Contact',
                'uri' => '/contact',
                'pages' => array(
                    'staff' => array(
                        'label' => 'Staff',
                        'uri' => '/staff'
                    ),
                    'contact' => array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    ),
                    'consultation' => array(
                        'label' => 'Free Consultation',
                        'uri' => '/consultation'
                    ),
                )
            ),

            // Member navigation
            'members-about' => array(
                'label' => 'About',
                'uri' => '#',
                'pages' => array(
                    'aaup' => array(
                        'label' => 'AAUP',
                        'uri' => '/aaup'
                    ),
                    'history' => array(
                        'label' => 'FCS History',
                        'uri' => '/history'
                    ),
                    'survey' => array(
                        'label' => 'Survey',
                        'uri' => '/survey'
                    ),
                    'research' => array(
                        'label' => 'Research',
                        'uri' => '/research'
                    ),
                )
            ),
            'renew' => array(
                'label' => 'Start',
                'uri' => '/renew',
                'class' => 'renew-nav'
            ),
            'data-collection' => array(
                'label' => 'Data Collection',
                'uri' => '#',
                'pages' => array(
                    'instructions' => array(
                        'label' => 'Instructions',
                        'uri' => '/instructions'
                    ),
                    'template' => array(
                        'label' => 'Template',
                        'uri' => '/data-entry/import'
                    ),
                    'overview' => array(
                        'label' => 'Overview',
                        'uri' => '/data-entry'
                    ),
                )
            ),
            'documentation' => array(
                'label' => 'Documentation',
                'uri' => '#',
                'pages' => array(
                    'instructions' => array(
                        'label' => 'Instructions',
                        'uri' => '/instructions'
                    ),
                    'webinars' => array(
                        'label' => 'Webinars',
                        'uri' => '/webinars'
                    ),
                    'faq' => array(
                        'label' => 'FAQ',
                        'uri' => '/faq'
                    ),
                    /*'submitted-values' => array(
                        'label' => 'Submitted Values',
                        'uri' => '/submitted-values'
                    ),
                    'dictionary' => array(
                        'label' => 'Data Dictionary',
                        'uri' => '/data-dictionary'
                    ),*/
                    'calculations' => array(
                        'label' => 'Calculations',
                        'uri' => '/calculations'
                    ),
                    'changes' => array(
                        'label' => 'Changes/Errata',
                        'uri' => '/changes'
                    ),
                )
            ),
            'members-results' => array(
                'label' => 'Results',
                'uri' => '#',
                'pages' => array(
                    'sample-results' => array(
                        'label' => 'Sample Results',
                        'uri' => '/sample-results'
                    ),
                    'order' => array(
                        'label' => 'Order',
                        'uri' => '/order'
                    ),
                    'consultation' => array(
                        'label' => 'Free Consultation',
                        'uri' => '/consultation'
                    ),
                )
            ),
            'reports' => getAAUPReportMenu(),
            'members-resources' => array(
                'label' => 'Resources',
                'uri' => '#',
                'pages' => array(
                    'help' => array(
                        'label' => 'Help',
                        'uri' => '/help'
                    ),
                    'presentations' => array(
                        'label' => 'Presentations',
                        'uri' => '/presentations'
                    ),
                    'publications' => array(
                        'label' => 'Publications',
                        'uri' => '/publications'
                    ),
                    'webinars' => array(
                        'label' => 'Webinars',
                        'uri' => '/webinars'
                    ),
                    'media' => array(
                        'label' => 'Media',
                        'uri' => '/media'
                    ),
                    'consultation' => array(
                        'label' => 'Free Consultation',
                        'uri' => '/consultation'
                    ),
                )
            ),
            'members-contact' => array(
                'label' => 'Contact',
                'uri' => '/contact',
                'pages' => array(
                    'contact' => array(
                        'label' => 'Contact Us',
                        'uri' => '/contact'
                    ),
                    'staff' => array(
                        'label' => 'Staff',
                        'uri' => '/staff'
                    ),
                    'order' => array(
                        'label' => 'Order',
                        'uri' => '/order'
                    ),
                    'consultation' => array(
                        'label' => 'Free Consultation',
                        'uri' => '/consultation'
                    ),
                )
            ),
            'account' => getAccountMenu(),
            'admin' => array(
                'label' => '<span class="glyphicon glyphicon-cog icon icon-cog adminMenuIcon"></span>',
                'uri' => '/admin',
                'resource' => 'adminMenu',
                'privilege' => 'view',
                'pages' => getAdminMenu()
            )
        ),
        'admin' => getAdminMenu()
    )
);



function getReportMenu()
{
    return array(
        'label' => 'Reports',
        'uri' => '/reports',
        'pages' => array(
            'institutional' => array(
                'label' => 'Institutional Reports',
                'uri' => '/reports/institutional'
            ),
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
            'executive' => array(
                'label' => 'Executive Report',
                'uri' => '/reports/executive'
            ),
            'peer' => array(
                'label' => 'Peer Comparison',
                'uri' => '/reports/peer'
            ),
            'best-performers' => array(
                'label' => 'Best Performers Report',
                'uri' => '/reports/best-performers'
            ),
            'strengths' => array(
                'label' => 'Strengths/Opportunities Report',
                'uri' => '/reports/strengths'
            ),
            'custom' => array(
                'label' => 'Custom Reports',
                'uri' => '/reports/custom'
            )
        )
    );
}

function getAAUPReportMenu()
{
    $menu = getReportMenu();

    // Change the label
    $menu['label'] = 'Results';

    // Remove reports AAUP doesn't use
    unset($menu['pages']['executive']);
    unset($menu['pages']['best-performers']);
    unset($menu['pages']['strengths']);
    unset($menu['pages']['summary']);

    // Add some stuff
    $newReportsPages = array();
    $newReportsPages['results-instructions'] = array(
        'label' => 'Results Instructions',
        'uri' => '/results-instructions'
    );

    $newReportsPages['sample-results'] = array(
        'label' => 'Sample Results',
        'uri' => '/sample-results'
    );

    $newReportsPages = array_merge($newReportsPages, $menu['pages']);

    $newReportsPages['free-consultation'] = array(
        'label' => 'Free Consultation',
        'uri' => '/consultation'
    );



    // Reorder
    $reorderedPages = array();

    $reorderedPages['results-instructions'] = $newReportsPages['results-instructions'];
    $reorderedPages['peer'] = $newReportsPages['peer'];
    $reorderedPages['custom'] = $newReportsPages['custom'];
    $reorderedPages['national'] = $newReportsPages['national'];
    $reorderedPages['system'] = $newReportsPages['system'];
    $reorderedPages['sample-results'] = $newReportsPages['sample-results'];
    $reorderedPages['free-consultation'] = $newReportsPages['free-consultation'];

    $newReportsPages = $reorderedPages;

    $menu['pages'] = $newReportsPages;

    return $menu;
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
            'peer-groups' => array(
                'label' => 'Manage Your Peer Groups',
                'route' => 'peer-groups'
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
        /*'pages' => array(

        )*/
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


function getAdminMenu()
{
    return array(
    'dashboard' => array(
        'label' => 'Memberships',
        'route' => 'admin'
    ),
    'institutions' => array(
        'label' => 'Institutions',
        'controller' => 'colleges',
        'action' => 'index',
        'route' => 'colleges'
    ),
    'studies' => array(
        'label' => 'Study Setup',
        'controller' => 'studies',
        'route' => 'studies'
    ),
    'issues/staff' => array(
        'label' => 'Data Issues',
        'route' => 'issues/staff'
    ),
    'reports' => array(
        'label' => 'Report Calculations',
        'route' => 'reports/calculate'
    ),
    'custom-reports' => array(
        'label' => 'Custom Report Admin',
        'route' => 'reports/custom/admin'
    ),
    'demographic-criteria' => array(
        'label' => 'Demographic Criteria',
        'route' => 'criteria'
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
        'label' => 'User Approval Queue',
        'route' => 'users/queue'
    ),
    array(
        'label' => 'Tools',
        'route' => 'tools',
    ),
    /*array(
        'label' => 'User',
        'route' => 'zfcuser',
        'controller' => 'zfcuser',
        'action' => 'index',
        'ulClass' => 'dropdown-menu',
    )*/
);

}
