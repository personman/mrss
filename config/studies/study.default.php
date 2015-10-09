<?php

// Default study config
return array(
    'from_email' => 'no-reply@jccc.edu',
    'cc_email' => 'michelletaylor@jccc.edu',
    'approver_email' => 'dfergu15@jccc.edu',
    'breakpoints' => '10,25,50,75,90',
    'layout' => 'layout.phtml',
    'favicon' => '/favicon.ico',
    'contact_instructions' => '',
    'css' => null,
    'css_print' => null,
    'header_title' => null,
    'user_role_choices' => 'viewer,contact,data', // Comma separated, no spaces
    'copyright' => "2004 - " . date('Y') . " Johnson County Community College",
    'footerLogo' => '<a href="/benchmarking-institute"><img src="/images/benchmark_logo.png" /></a>',
    'footerSocial' => '<a href="http://twitter.com/EdBenchmark" title="Follow us on Twitter" target="_blank">
                    <img src="/images/48x48_twitter.png" alt="Follow us on Twitter" />
                </a>
                <a href="https://www.linkedin.com/groups/National-Higher-Education-Benchmarking-Institute-2022361" title="Join our LinkedIn Group" target="_blank">
                    <img src="/images/48x48_linkedin.png" alt="Join our LinkedIn Group" />
                </a>'
);
