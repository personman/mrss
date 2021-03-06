<?php

return array(
    'from_email' => 'info@envisio.com',
    'from_email_name' => 'Envisio Benchmarking',
    'reply_to_email' => 'mbell@envisio.com',
    'reply_to_email_name' => 'Mike Bell',
    'cc_email' => 'mbell@envisio.com',
    'approver_email' => 'mbell@envisio.com',
    'layout' => 'govbenchmark.phtml',
    'css' => 'nccbp.css',
    'welcome_email' => 'welcome-envisio',
    'favicon' => '/envisio-favicon.ico',
    'navigation' => 'envisio_navigation',
    'round_data_entry_to' => 2,
    'export_template' => 'govbenchmark-template.xlsx',
    'news_page_id' => 65,
    'enable_min_max_breakpoints' => true,
    'allow_public_custom_report' => true,
    'benchmark_completion_heatmap' => false,
    'college_report_access_checkbox' => true,
    'percent_change_report_columns' => array(
    ),
    'show_peer_data_you_did_not_submit' => true, // Temporary!!
    'percent_chart_scale_1_100' => false,
    'use_structures' => true,
    'primary_systems' => array(2, 3), // Envisio's national city and county networks
    'system_benchmarks' => true,
    'system_label' => 'network',
    'show_institution_in_report_heading' => true,
    'anonymous_peers' => false,
    'allow_peer_report_download' => false,
    'support_email' => 'govbenchmark@envisio.com',
    'peer_percentiles' => false,
    'prior_year_edits' => true,
    'min_peers' => 0,
    'include_canada' => true,
    'chart_colors' => '#a2ce5d|#007ca0|#828387|#fbb41e',
    'allow_custom_colors' => true,
    'system_logos' => array(
        1 => 'ValleyBenchmarkCities_Color.png'
    ),
    'hardcode_colors' => true,
    'national_report_name' => 'Benchmark Report',
    'your_institution_label' => 'Your Jurisdiction',
    'institution_label' => 'Jurisdiction',
    'institutions_label' => 'Jurisdictions',
    'benchmark_label' => 'measure',
    'form_label' => 'service area',
    'use_gravatar' => true,
    'login_extra_text' => '<a href="https://www.envisio.com/govbenchmark/lite-signup">Not a member? Sign up now.</a>',
    'contact_recipient' => array('support@envisio.com'),
    'contact_instructions' => 'You can also reach us by email: Sarah Delaney, Customer Solutions Manager, support@envisio.com',
    'copyright' => date('Y') . " Envisio",
    'footerLogo' => '',
    'footerSocial' => '',
    'freemium' => true,
    'muut-disable' => array(
        'name' => 'govbenchmark-community',
        'key' => 'UoyCJIKsWo',
        'secret' => 'KMzk9tfsl4uvQUrrLt2apIsh'
    )
);
