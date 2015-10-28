<?php

// AAUP - FCS
return array(
    'from_email' => 'jbarnshaw@aaup.org',
    //'cc_email' => 'jbarnshaw@aaup.org',
    'cc_email' => 'dfergu15@jccc.edu',
    //'approver_email' => 'jbarnshaw@aaup.org',
    'approver_email' => 'dfergu15@jccc.edu',
    'breakpoints' => '20,40,60,80',
    'layout' => 'nccbp.phtml',
    'favicon' => '/favicon.ico',
    'contact_instructions' => null,
    'css' => 'aaup.css',
    'css_print' => null,
	'header_title' => 'Faculty<br>Compensation<br>Survey',
    'user_role_choices' => 'viewer',
    'copyright' => "1915 - " . date('Y') . " American Association of University Professors",
    'footerLogo' => '',
    'footerSocial' => '',
    'data_entry_templates' => array(
        3 => 'fcs/full-time-salary.phtml',
        38 => 'student-services-grid.phtml',
        39 => 'academic-support-grid.phtml',
        40 => 'demographics.phtml',
        44 => 'managerial-grid-at.phtml',
        42 => 'student-success-metrics.phtml'
    )
);

