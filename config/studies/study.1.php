<?php

return array(
    'layout' => 'nccbp.phtml',
    'css' => 'nccbp.css',
    'navigation' => 'nccbp_navigation',
    'validation_class' => 'NCCBPValidation',
    'validation_require_note' => false,
    'benchmark_completion_heatmap' => true,
    'form_to_exclude_from_strengths' => 1,
    'treat_null_as_zero_for_add_sub' => false,
    'percent_change_report_columns' => array(
        'ft_cr_head',
        'tuition_fees',
        'ft_minus4_perc_completed',
        'pt_minus4_perc_completed',
        'ft_perc_transf',
        'pt_perc_transf',
        'ft_minus4_perc_comp_or_transf',
        'pt_minus4_perc_comp_or_transf',
        'ft_minus7_perc_completed',
        'pt_minus7_perc_completed',
        'percminus7_transf',
        'pt_percminus7_tran',
        'ft_minus7_perc_comp_or_transf',
        'pt_minus7_perc_comp_or_transf',
        'cst_crh',
        'cst_fte_stud',
        // Removing form NC9
        //'institutional_demographics_certifications_awarded',
        //'institutional_demographics_licenses_awarded',
        //'institutional_demographics_certificates_awarded',
        //'total_cert_lic_certificates',
        //'credentials_percent_unduplicated_enrollment',
        //'institutional_demographics_ged_awarded',
        //'all_ce_courses_canceled'
    ),
    'contact_instructions' => 'You can also reach us by phone: Dr. Lou Guthrie, Director, 913-469-8500 x4019 or Michelle Taylor, Senior Research &amp; Data Analyst, 913-469-8500 x3648.',
    'muut' => array(
        'name' => 'benchmarking-institute',
        'key' => 'EkkvhumnsD',
        'secret' => 'C5QeOZNbJ8HZiC315wblaVDk'
    )
);
