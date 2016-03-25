<?php

// Default study config
return array(
    'from_email' => 'no-reply@jccc.edu',
    'from_email_name' => 'Michelle Taylor',
    'cc_email' => 'michelletaylor@jccc.edu',
    'approver_email' => 'dfergu15@jccc.edu',
    'data_entry_templates' => array(),
    'breakpoints' => '10,25,50,75,90',
    'show_peer_data_you_did_not_submit' => false,
    'college_report_access_checkbox' => false,
    'layout' => 'layout.phtml',
    'navigation' => 'navigation',
    'favicon' => '/favicon.ico',
    'contact_instructions' => '',
    'contact_recipient' => null,
    'css' => null,
    'css_print' => null,
    'header_title' => null,
    'data_entry_layout' => array(),
    'export_sheet_names' => null,
    'validation_class' => null,
    'free_to_join' => false,
    'agreement_template' => null,
    'welcome_email' => 'welcome',
    'outlier_email' => 'outliers.email',
    'outlier_email_none' => 'outliers.email.none',
    'default_user_state' => 1, // 0 = pending, 1 = approved
    'user_role_choices' => 'viewer,contact,data', // Comma separated, no spaces
    'anonymous_peers' => true,
    'copyright' => "2004 - " . date('Y') . " Johnson County Community College",
    'footerLogo' => '<a href="/benchmarking-institute"><img src="/images/benchmark_logo.png" /></a>',
    'footerSocial' => '<a href="http://twitter.com/EdBenchmark" title="Follow us on Twitter" target="_blank">
                    <img src="/images/48x48_twitter.png" alt="Follow us on Twitter" />
                </a>
                <a href="https://www.linkedin.com/groups/National-Higher-Education-Benchmarking-Institute-2022361" title="Join our LinkedIn Group" target="_blank">
                    <img src="/images/48x48_linkedin.png" alt="Join our LinkedIn Group" />
                </a>'
);
