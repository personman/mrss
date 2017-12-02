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
    'allow_public_custom_report' => true,
    'benchmark_completion_heatmap' => true,
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
    'peer_percentiles' => false,
    'prior_year_edits' => true,
    'min_peers' => 0,
    'include_canada' => true,
    'chart_colors' => '#a2ce5d|#007ca0|#828387|#fbb41e',
    'national_report_name' => 'Benchmark Report',
    'your_institution_label' => 'Your City',
    'institution_label' => 'City',
    'institutions_label' => 'Cities',
    'benchmark_label' => 'measure',
    'form_label' => 'service area',
    'use_gravatar' => true,
    'contact_instructions' => 'You can also reach us by phone: Mike Daniel, Director govBenchmark, 604-256-7055',
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
