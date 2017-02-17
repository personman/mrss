<?php

// AAUP - FCS
return array(
    'from_email' => 'aaupfcs@aaup.org',
    'from_email_name' => 'John Barnshaw',
    //'cc_email' => 'jbarnshaw@aaup.org',
    'cc_email' => 'dfergu15@jccc.edu',
    //'approver_email' => 'jbarnshaw@aaup.org',
    'approver_email' => 'dfergu15@jccc.edu',
    'favicon' => '/images/aaup-favicon.ico',
    'breakpoints' => '20,40,60,80',
    'show_peer_data_you_did_not_submit' => true,
    'college_report_access_checkbox' => true,
    'layout' => 'nccbp.phtml',
    'navigation' => 'fcs_navigation',
    'contact_instructions' => null,
    'contact_recipient' => array('jbarnshaw@aaup.org', 'sdunietz@aaup.org'),
    //'contact_recipient' => array('dfergu15@jccc.edu'),
    'css' => 'aaup.css',
    'css_print' => null,
    'validation_class' => 'FCSValidation',
	'header_title' => 'Faculty<br>Compensation<br>Survey',
    'free_to_join' => true,
    'agreement_template' => 'agreement-fcs',
    'custom_excel_template' => 'data/imports/aaup-export.xlsx',
    'welcome_email' => 'welcome-aaup',
    'outlier_email' => 'outliers.email.aaup',
    'outlier_email_none' => 'outliers.email.none.aaup', // Send all AAUP users the same email
    'default_user_state' => 0, // 0 = pending, 1 = approved
    'user_role_choices' => 'viewer',
    'anonymous_peers' => false,
    'head_logo_url' => 'http://www.aaup.org',
    'logged_out_header_button' => '<a href="/consultation" class="btn schedule-btn" role="button" id="schedule-demo">FREE CONSULTATION</a>',
    'copyright' => date('Y') . " American Association of University Professors",
    'footerLogo' => '<span>Powered by</span> <br><a href="http://nccbp.org/benchmarking-institute" title="National Higher Eduction Benchmarking Institute @ Johnson County Community College"><img src="/images/benchmark_logo.png" /></a>',
    'percent_change_report_columns' => array(
        'ft_average_professor_salary',
        'ft_average_associate_professor_salary',
        'ft_average_assistant_professor_salary',
        'ft_average_instructor_salary',
        'ft_average_all_ranks_salary_historical'
    ),
    'footerSocial' => '',
    'data_entry_templates' => array(
        //3 => 'fcs/full-time-salary.phtml',
        3 => 'aaup/observation/full-time-salary.phtml',
        4 => 'aaup/observation/full-time-benefits.phtml',
        5 => 'aaup/observation/full-time-continuing.phtml',
        6 => 'aaup/observation/administrative.phtml',
        7 => 'aaup/observation/part-time.phtml'
    ),
    'export_sheet_names' => array(
        // benchmarkGroup id => excel sheet name
        2 => array(
            'sheetName' => 'Form 1',
            'sectionStartingCells' => array(
                0 => 'C15'
            )
        ),
        3 => array(
            'sheetName' => 'Form 2',
            'sectionStartingCells' => array(
                0 => 'B7',
                //1 => 'G7',
                1 => 'B15',
                //3 => 'G15',
            ),
            'extra' => array(
                //'institution_conversion_factor' => 'G14'
            )
        ),
        4 => array(
            'sheetName' => 'Form 3',
            'sectionStartingCells' => array(
                0 => 'B6',
                1 => 'B19',
            ),
        ),
        '4b' => array(
            'sheetName' => 'Form 3',
            'sectionStartingCells' => array(
                0 => 'P6',
                1 => 'P19'
            ),
        ),
        5 => array(
            'sheetName' => 'Form 4',
            'sectionStartingCells' => array(
                0 => 'B6',
                1 => 'B16'
            )
        ),
        6 => array(
            'sheetName' => 'Form 5',
            'sectionStartingCells' => array(
                0 => 'B7',
            )
        ),
        7 => array(
            'sheetName' => 'Form 6',
            'sectionStartingCells' => array(
                0 => 'B8',
            )
        )
    ),
    'data_entry_layout' => array(
        // Form 1: Institutional information
        2 => array(
            array(
                'rows' => array(
                    // the null rows account for skipped rows in Excel from 1
                    array('institution_control'),
                    array(null),
                    array('institution_sector'),
                    array(null),
                    array('institution_aaup_category'),
                    array(null),
                    array('carnegie_basic'),
                    array(null),
                    array('institution_system'),
                    array(null),
                    array('institution_highest_degree'),
                    array(null),
                    array('institution_grants_medical_degree'),
                    array(null),
                    array('institution_publication_footnote'),
                    array(null),
                    array('institution_conversion_factor'),
                    array(null),
                    array('institution_eligible_cip_codes'),
                    array(null),
                    array('institution_campuses'),
                    array(null),
                    array('institution_comments'),
                    array(null),
                    array('institution_add_comment'),
                    array(null),
                    array('institution_accurarcy_confirmation'),
                    array(null),
                    array('institution_deadline_change'),
                    array(null),
                    array('institution_non_submission'),
                    array(null),
                    array('institution_tenure_system'),
                    array(null),
                    array('institution_faculty_union'),
                    array(null),
                    array('institution_part_time_benefits'),
                )
            )
        ),
        // Full-time Salary
        3 => array(
            // Faculty
            array(
                'rows' => array(
                    'Section 1. Faculty on 9-month Contracts (i.e., regardless of number of salary installments)',
                    'Professor' => array(
                        'ft_male_professor_number_9_month',
                        'ft_male_professor_salaries_9_month',
                        'ft_male_professor_ntt_9_month',
                        'ft_male_professor_tt_9_month',
                        'ft_male_professor_t_9_month',
                        'ft_female_professor_number_9_month',
                        'ft_female_professor_salaries_9_month',
                        'ft_female_professor_ntt_9_month',
                        'ft_female_professor_tt_9_month',
                        'ft_female_professor_t_9_month'
                    ),
                    'Associate' => array(
                        'ft_male_associate_professor_number_9_month',
                        'ft_male_associate_professor_salaries_9_month',
                        'ft_male_associate_professor_ntt_9_month',
                        'ft_male_associate_professor_tt_9_month',
                        'ft_male_associate_professor_t_9_month',
                        'ft_female_associate_professor_number_9_month',
                        'ft_female_associate_professor_salaries_9_month',
                        'ft_female_associate_professor_ntt_9_month',
                        'ft_female_associate_professor_tt_9_month',
                        'ft_female_associate_professor_t_9_month'
                    ),
                    'Assistant' => array(
                        'ft_male_assistant_professor_number_9_month',
                        'ft_male_assistant_professor_salaries_9_month',
                        'ft_male_assistant_professor_ntt_9_month',
                        'ft_male_assistant_professor_tt_9_month',
                        'ft_male_assistant_professor_t_9_month',
                        'ft_female_assistant_professor_number_9_month',
                        'ft_female_assistant_professor_salaries_9_month',
                        'ft_female_assistant_professor_ntt_9_month',
                        'ft_female_assistant_professor_tt_9_month',
                        'ft_female_assistant_professor_t_9_month'
                    ),
                    'Instructor' => array(
                        'ft_male_instructor_number_9_month',
                        'ft_male_instructor_salaries_9_month',
                        'ft_male_instructor_ntt_9_month',
                        'ft_male_instructor_tt_9_month',
                        'ft_male_instructor_t_9_month',
                        'ft_female_instructor_number_9_month',
                        'ft_female_instructor_salaries_9_month',
                        'ft_female_instructor_ntt_9_month',
                        'ft_female_instructor_tt_9_month',
                        'ft_female_instructor_t_9_month'
                    ),
                    'Lecturer' => array(
                        'ft_male_lecturer_number_9_month',
                        'ft_male_lecturer_salaries_9_month',
                        'ft_male_lecturer_ntt_9_month',
                        'ft_male_lecturer_tt_9_month',
                        'ft_male_lecturer_t_9_month',
                        'ft_female_lecturer_number_9_month',
                        'ft_female_lecturer_salaries_9_month',
                        'ft_female_lecturer_ntt_9_month',
                        'ft_female_lecturer_tt_9_month',
                        'ft_female_lecturer_t_9_month'
                    ),
                    'No Rank' => array(
                        'ft_male_no_rank_number_9_month',
                        'ft_male_no_rank_salaries_9_month',
                        'ft_male_no_rank_ntt_9_month',
                        'ft_male_no_rank_tt_9_month',
                        'ft_male_no_rank_t_9_month',
                        'ft_female_no_rank_number_9_month',
                        'ft_female_no_rank_salaries_9_month',
                        'ft_female_no_rank_ntt_9_month',
                        'ft_female_no_rank_tt_9_month',
                        'ft_female_no_rank_t_9_month'
                    ),
                    '[total_row_9]'
                ),
            ),
            array(
                'rows' => array(

                    'Section 2. Faculty on 12-month Contracts (i.e., regardless of number of salary installments)',
                    'Professor' => array(
                        'ft_male_professor_number_12_month',
                        'ft_male_professor_salaries_12_month',
                        'ft_male_professor_ntt_12_month',
                        'ft_male_professor_tt_12_month',
                        'ft_male_professor_t_12_month',
                        'ft_female_professor_number_12_month',
                        'ft_female_professor_salaries_12_month',
                        'ft_female_professor_ntt_12_month',
                        'ft_female_professor_tt_12_month',
                        'ft_female_professor_t_12_month'
                    ),
                    'Associate' => array(
                        'ft_male_associate_professor_number_12_month',
                        'ft_male_associate_professor_salaries_12_month',
                        'ft_male_associate_professor_ntt_12_month',
                        'ft_male_associate_professor_tt_12_month',
                        'ft_male_associate_professor_t_12_month',
                        'ft_female_associate_professor_number_12_month',
                        'ft_female_associate_professor_salaries_12_month',
                        'ft_female_associate_professor_ntt_12_month',
                        'ft_female_associate_professor_tt_12_month',
                        'ft_female_associate_professor_t_12_month'
                    ),
                    'Assistant' => array(
                        'ft_male_assistant_professor_number_12_month',
                        'ft_male_assistant_professor_salaries_12_month',
                        'ft_male_assistant_professor_ntt_12_month',
                        'ft_male_assistant_professor_tt_12_month',
                        'ft_male_assistant_professor_t_12_month',
                        'ft_female_assistant_professor_number_12_month',
                        'ft_female_assistant_professor_salaries_12_month',
                        'ft_female_assistant_professor_ntt_12_month',
                        'ft_female_assistant_professor_tt_12_month',
                        'ft_female_assistant_professor_t_12_month'
                    ),
                    'Instructor' => array(
                        'ft_male_instructor_number_12_month',
                        'ft_male_instructor_salaries_12_month',
                        'ft_male_instructor_ntt_12_month',
                        'ft_male_instructor_tt_12_month',
                        'ft_male_instructor_t_12_month',
                        'ft_female_instructor_number_12_month',
                        'ft_female_instructor_salaries_12_month',
                        'ft_female_instructor_ntt_12_month',
                        'ft_female_instructor_tt_12_month',
                        'ft_female_instructor_t_12_month'
                    ),
                    'Lecturer' => array(
                        'ft_male_lecturer_number_12_month',
                        'ft_male_lecturer_salaries_12_month',
                        'ft_male_lecturer_ntt_12_month',
                        'ft_male_lecturer_tt_12_month',
                        'ft_male_lecturer_t_12_month',
                        'ft_female_lecturer_number_12_month',
                        'ft_female_lecturer_salaries_12_month',
                        'ft_female_lecturer_ntt_12_month',
                        'ft_female_lecturer_tt_12_month',
                        'ft_female_lecturer_t_12_month'
                    ),
                    'No Rank' => array(
                        'ft_male_no_rank_number_12_month',
                        'ft_male_no_rank_salaries_12_month',
                        'ft_male_no_rank_ntt_12_month',
                        'ft_male_no_rank_tt_12_month',
                        'ft_male_no_rank_t_12_month',
                        'ft_female_no_rank_number_12_month',
                        'ft_female_no_rank_salaries_12_month',
                        'ft_female_no_rank_ntt_12_month',
                        'ft_female_no_rank_tt_12_month',
                        'ft_female_no_rank_t_12_month'
                    ),
                    '[total_row_12]',
                    'Section 3.  9-Month Contracts plus 11 or 12-Month Contracts (Converts 11 or 12-month salaries and calculates automatically.)',
                    '[total_rows_9_12]'
                ),
            ),
        ),
        // Full-time benefits
        4 => array(
            // Faculty
            array(
                'rows' => array(
                    'Faculty on 9-Month Contracts (i.e., regardless of number of installments)',
                    'Retirement' => array(
                        'ft_retirement_expenditure_professor_9_month',
                        'ft_retirement_covered_professor_9_month',
                        'ft_retirement_expenditure_associate_professor_9_month',
                        'ft_retirement_covered_associate_professor_9_month',
                        'ft_retirement_expenditure_assistant_professor_9_month',
                        'ft_retirement_covered_assistant_professor_9_month',
                        'ft_retirement_expenditure_instructor_9_month',
                        'ft_retirement_covered_instructor_9_month',
                        'ft_retirement_expenditure_lecturer_9_month',
                        'ft_retirement_covered_lecturer_9_month',
                        'ft_retirement_expenditure_no_rank_9_month',
                        'ft_retirement_covered_no_rank_9_month',
                    ),
                    'Medical' => array(
                        'ft_medical_expenditure_professor_9_month',
                        'ft_medical_covered_professor_9_month',
                        'ft_medical_expenditure_associate_professor_9_month',
                        'ft_medical_covered_associate_professor_9_month',
                        'ft_medical_expenditure_assistant_professor_9_month',
                        'ft_medical_covered_assistant_professor_9_month',
                        'ft_medical_expenditure_instructor_9_month',
                        'ft_medical_covered_instructor_9_month',
                        'ft_medical_expenditure_lecturer_9_month',
                        'ft_medical_covered_lecturer_9_month',
                        'ft_medical_expenditure_no_rank_9_month',
                        'ft_medical_covered_no_rank_9_month',
                    ),
                    'Dental' => array(
                        'ft_dental_expenditure_professor_9_month',
                        'ft_dental_covered_professor_9_month',
                        'ft_dental_expenditure_associate_professor_9_month',
                        'ft_dental_covered_associate_professor_9_month',
                        'ft_dental_expenditure_assistant_professor_9_month',
                        'ft_dental_covered_assistant_professor_9_month',
                        'ft_dental_expenditure_instructor_9_month',
                        'ft_dental_covered_instructor_9_month',
                        'ft_dental_expenditure_lecturer_9_month',
                        'ft_dental_covered_lecturer_9_month',
                        'ft_dental_expenditure_no_rank_9_month',
                        'ft_dental_covered_no_rank_9_month',
                    ),
                    '(Optional) Combined Medical w/ Dental' => array(
                        'ft_combined_medical_dental_expenditure_professor_9_month',
                        'ft_combined_medical_dental_covered_professor_9_month',
                        'ft_combined_medical_dental_expenditure_associate_prof_9_month',
                        'ft_combined_medical_dental_covered_associate_professor_9_month',
                        'ft_combined_medical_dental_expenditure_assistant_prof_9_month',
                        'ft_combined_medical_dental_covered_assistant_professor_9_month',
                        'ft_combined_medical_dental_expenditure_instructor_9_month',
                        'ft_combined_medical_dental_covered_instructor_9_month',
                        'ft_combined_medical_dental_expenditure_lecturer_9_month',
                        'ft_combined_medical_dental_covered_lecturer_9_month',
                        'ft_combined_medical_dental_expenditure_no_rank_9_month',
                        'ft_combined_medical_dental_covered_no_rank_9_month',
                    ),
                    'Disability' => array(
                        'ft_disability_expenditure_professor_9_month',
                        'ft_disability_covered_professor_9_month',
                        'ft_disability_expenditure_associate_professor_9_month',
                        'ft_disability_covered_associate_professor_9_month',
                        'ft_disability_expenditure_assistant_professor_9_month',
                        'ft_disability_covered_assistant_professor_9_month',
                        'ft_disability_expenditure_instructor_9_month',
                        'ft_disability_covered_instructor_9_month',
                        'ft_disability_expenditure_lecturer_9_month',
                        'ft_disability_covered_lecturer_9_month',
                        'ft_disability_expenditure_no_rank_9_month',
                        'ft_disability_covered_no_rank_9_month',
                    ),
                    'Tuition' => array(
                        'ft_tuition_expenditure_professor_9_month',
                        'ft_tuition_covered_professor_9_month',
                        'ft_tuition_expenditure_associate_professor_9_month',
                        'ft_tuition_covered_associate_professor_9_month',
                        'ft_tuition_expenditure_assistant_professor_9_month',
                        'ft_tuition_covered_assistant_professor_9_month',
                        'ft_tuition_expenditure_instructor_9_month',
                        'ft_tuition_covered_instructor_9_month',
                        'ft_tuition_expenditure_lecturer_9_month',
                        'ft_tuition_covered_lecturer_9_month',
                        'ft_tuition_expenditure_no_rank_9_month',
                        'ft_tuition_covered_no_rank_9_month',
                    ),
                    'FICA' => array(
                        'ft_fica_expenditure_professor_9_month',
                        'ft_fica_covered_professor_9_month',
                        'ft_fica_expenditure_associate_professor_9_month',
                        'ft_fica_covered_associate_professor_9_month',
                        'ft_fica_expenditure_assistant_professor_9_month',
                        'ft_fica_covered_assistant_professor_9_month',
                        'ft_fica_expenditure_instructor_9_month',
                        'ft_fica_covered_instructor_9_month',
                        'ft_fica_expenditure_lecturer_9_month',
                        'ft_fica_covered_lecturer_9_month',
                        'ft_fica_expenditure_no_rank_9_month',
                        'ft_fica_covered_no_rank_9_month',
                    ),
                    'Unemployment' => array(
                        'ft_unemployment_expenditure_professor_9_month',
                        'ft_unemployment_covered_professor_9_month',
                        'ft_unemployment_expenditure_associate_professor_9_month',
                        'ft_unemployment_covered_associate_professor_9_month',
                        'ft_unemployment_expenditure_assistant_professor_9_month',
                        'ft_unemployment_covered_assistant_professor_9_month',
                        'ft_unemployment_expenditure_instructor_9_month',
                        'ft_unemployment_covered_instructor_9_month',
                        'ft_unemployment_expenditure_lecturer_9_month',
                        'ft_unemployment_covered_lecturer_9_month',
                        'ft_unemployment_expenditure_no_rank_9_month',
                        'ft_unemployment_covered_no_rank_9_month',
                    ),
                    'Group Life' => array(
                        'ft_group_life_expenditure_professor_9_month',
                        'ft_group_life_covered_professor_9_month',
                        'ft_group_life_expenditure_associate_professor_9_month',
                        'ft_group_life_covered_associate_professor_9_month',
                        'ft_group_life_expenditure_assistant_professor_9_month',
                        'ft_group_life_covered_assistant_professor_9_month',
                        'ft_group_life_expenditure_instructor_9_month',
                        'ft_group_life_covered_instructor_9_month',
                        'ft_group_life_expenditure_lecturer_9_month',
                        'ft_group_life_covered_lecturer_9_month',
                        'ft_group_life_expenditure_no_rank_9_month',
                        'ft_group_life_covered_no_rank_9_month',
                    ),
                    'Worker\'s Comp.' => array(
                        'ft_worker_comp_expenditure_professor_9_month',
                        'ft_worker_comp_covered_professor_9_month',
                        'ft_worker_comp_expenditure_associate_professor_9_month',
                        'ft_worker_comp_covered_associate_professor_9_month',
                        'ft_worker_comp_expenditure_assistant_professor_9_month',
                        'ft_worker_comp_covered_assistant_professor_9_month',
                        'ft_worker_comp_expenditure_instructor_9_month',
                        'ft_worker_comp_covered_instructor_9_month',
                        'ft_worker_comp_expenditure_lecturer_9_month',
                        'ft_worker_comp_covered_lecturer_9_month',
                        'ft_worker_comp_expenditure_no_rank_9_month',
                        'ft_worker_comp_covered_no_rank_9_month',
                    ),
                    'Other' => array(
                        'ft_other_expenditure_professor_9_month',
                        'ft_other_covered_professor_9_month',
                        'ft_other_expenditure_associate_professor_9_month',
                        'ft_other_covered_associate_professor_9_month',
                        'ft_other_expenditure_assistant_professor_9_month',
                        'ft_other_covered_assistant_professor_9_month',
                        'ft_other_expenditure_instructor_9_month',
                        'ft_other_covered_instructor_9_month',
                        'ft_other_expenditure_lecturer_9_month',
                        'ft_other_covered_lecturer_9_month',
                        'ft_other_expenditure_no_rank_9_month',
                        'ft_other_covered_no_rank_9_month',
                    ),
                    '[total_row_9]'

                ),
            ),
            array(
                'rows' => array(
                    'Faculty on 12-Month Contracts (i.e., on actual basis, no conversion)',
                    'Retirement' => array(
                        'ft_retirement_expenditure_professor_12_month',
                        'ft_retirement_covered_professor_12_month',
                        'ft_retirement_expenditure_associate_professor_12_month',
                        'ft_retirement_covered_associate_professor_12_month',
                        'ft_retirement_expenditure_assistant_professor_12_month',
                        'ft_retirement_covered_assistant_professor_12_month',
                        'ft_retirement_expenditure_instructor_12_month',
                        'ft_retirement_covered_instructor_12_month',
                        'ft_retirement_expenditure_lecturer_12_month',
                        'ft_retirement_covered_lecturer_12_month',
                        'ft_retirement_expenditure_no_rank_12_month',
                        'ft_retirement_covered_no_rank_12_month',
                    ),
                    'Medical' => array(
                        'ft_medical_expenditure_professor_12_month',
                        'ft_medical_covered_professor_12_month',
                        'ft_medical_expenditure_associate_professor_12_month',
                        'ft_medical_covered_associate_professor_12_month',
                        'ft_medical_expenditure_assistant_professor_12_month',
                        'ft_medical_covered_assistant_professor_12_month',
                        'ft_medical_expenditure_instructor_12_month',
                        'ft_medical_covered_instructor_12_month',
                        'ft_medical_expenditure_lecturer_12_month',
                        'ft_medical_covered_lecturer_12_month',
                        'ft_medical_expenditure_no_rank_12_month',
                        'ft_medical_covered_no_rank_12_month',
                    ),
                    'Dental' => array(
                        'ft_dental_expenditure_professor_12_month',
                        'ft_dental_covered_professor_12_month',
                        'ft_dental_expenditure_associate_professor_12_month',
                        'ft_dental_covered_associate_professor_12_month',
                        'ft_dental_expenditure_assistant_professor_12_month',
                        'ft_dental_covered_assistant_professor_12_month',
                        'ft_dental_expenditure_instructor_12_month',
                        'ft_dental_covered_instructor_12_month',
                        'ft_dental_expenditure_lecturer_12_month',
                        'ft_dental_covered_lecturer_12_month',
                        'ft_dental_expenditure_no_rank_12_month',
                        'ft_dental_covered_no_rank_12_month',
                    ),
                    '(Optional) Combined Medical w/ Dental' => array(
                        'ft_combined_medical_dental_expenditure_professor_12_month',
                        'ft_combined_medical_dental_covered_professor_12_month',
                        'ft_combined_medical_dental_expenditure_associate_prof_12_month',
                        'ft_combined_medical_dental_covered_associate_professor_12_month',
                        'ft_combined_medical_dental_expenditure_assistant_prof_12_month',
                        'ft_combined_medical_dental_covered_assistant_professor_12_month',
                        'ft_combined_medical_dental_expenditure_instructor_12_month',
                        'ft_combined_medical_dental_covered_instructor_12_month',
                        'ft_combined_medical_dental_expenditure_lecturer_12_month',
                        'ft_combined_medical_dental_covered_lecturer_12_month',
                        'ft_combined_medical_dental_expenditure_no_rank_12_month',
                        'ft_combined_medical_dental_covered_no_rank_12_month',
                    ),
                    'Disability' => array(
                        'ft_disability_expenditure_professor_12_month',
                        'ft_disability_covered_professor_12_month',
                        'ft_disability_expenditure_associate_professor_12_month',
                        'ft_disability_covered_associate_professor_12_month',
                        'ft_disability_expenditure_assistant_professor_12_month',
                        'ft_disability_covered_assistant_professor_12_month',
                        'ft_disability_expenditure_instructor_12_month',
                        'ft_disability_covered_instructor_12_month',
                        'ft_disability_expenditure_lecturer_12_month',
                        'ft_disability_covered_lecturer_12_month',
                        'ft_disability_expenditure_no_rank_12_month',
                        'ft_disability_covered_no_rank_12_month',
                    ),
                    'Tuition' => array(
                        'ft_tuition_expenditure_professor_12_month',
                        'ft_tuition_covered_professor_12_month',
                        'ft_tuition_expenditure_associate_professor_12_month',
                        'ft_tuition_covered_associate_professor_12_month',
                        'ft_tuition_expenditure_assistant_professor_12_month',
                        'ft_tuition_covered_assistant_professor_12_month',
                        'ft_tuition_expenditure_instructor_12_month',
                        'ft_tuition_covered_instructor_12_month',
                        'ft_tuition_expenditure_lecturer_12_month',
                        'ft_tuition_covered_lecturer_12_month',
                        'ft_tuition_expenditure_no_rank_12_month',
                        'ft_tuition_covered_no_rank_12_month',
                    ),
                    'FICA' => array(
                        'ft_fica_expenditure_professor_12_month',
                        'ft_fica_covered_professor_12_month',
                        'ft_fica_expenditure_associate_professor_12_month',
                        'ft_fica_covered_associate_professor_12_month',
                        'ft_fica_expenditure_assistant_professor_12_month',
                        'ft_fica_covered_assistant_professor_12_month',
                        'ft_fica_expenditure_instructor_12_month',
                        'ft_fica_covered_instructor_12_month',
                        'ft_fica_expenditure_lecturer_12_month',
                        'ft_fica_covered_lecturer_12_month',
                        'ft_fica_expenditure_no_rank_12_month',
                        'ft_fica_covered_no_rank_12_month',
                    ),
                    'Unemployment' => array(
                        'ft_unemployment_expenditure_professor_12_month',
                        'ft_unemployment_covered_professor_12_month',
                        'ft_unemployment_expenditure_associate_professor_12_month',
                        'ft_unemployment_covered_associate_professor_12_month',
                        'ft_unemployment_expenditure_assistant_professor_12_month',
                        'ft_unemployment_covered_assistant_professor_12_month',
                        'ft_unemployment_expenditure_instructor_12_month',
                        'ft_unemployment_covered_instructor_12_month',
                        'ft_unemployment_expenditure_lecturer_12_month',
                        'ft_unemployment_covered_lecturer_12_month',
                        'ft_unemployment_expenditure_no_rank_12_month',
                        'ft_unemployment_covered_no_rank_12_month',
                    ),
                    'Group Life' => array(
                        'ft_group_life_expenditure_professor_12_month',
                        'ft_group_life_covered_professor_12_month',
                        'ft_group_life_expenditure_associate_professor_12_month',
                        'ft_group_life_covered_associate_professor_12_month',
                        'ft_group_life_expenditure_assistant_professor_12_month',
                        'ft_group_life_covered_assistant_professor_12_month',
                        'ft_group_life_expenditure_instructor_12_month',
                        'ft_group_life_covered_instructor_12_month',
                        'ft_group_life_expenditure_lecturer_12_month',
                        'ft_group_life_covered_lecturer_12_month',
                        'ft_group_life_expenditure_no_rank_12_month',
                        'ft_group_life_covered_no_rank_12_month',
                    ),
                    'Worker\'s Comp.' => array(
                        'ft_worker_comp_expenditure_professor_12_month',
                        'ft_worker_comp_covered_professor_12_month',
                        'ft_worker_comp_expenditure_associate_professor_12_month',
                        'ft_worker_comp_covered_associate_professor_12_month',
                        'ft_worker_comp_expenditure_assistant_professor_12_month',
                        'ft_worker_comp_covered_assistant_professor_12_month',
                        'ft_worker_comp_expenditure_instructor_12_month',
                        'ft_worker_comp_covered_instructor_12_month',
                        'ft_worker_comp_expenditure_lecturer_12_month',
                        'ft_worker_comp_covered_lecturer_12_month',
                        'ft_worker_comp_expenditure_no_rank_12_month',
                        'ft_worker_comp_covered_no_rank_12_month',
                    ),
                    'Other' => array(
                        'ft_other_expenditure_professor_12_month',
                        'ft_other_covered_professor_12_month',
                        'ft_other_expenditure_associate_professor_12_month',
                        'ft_other_covered_associate_professor_12_month',
                        'ft_other_expenditure_assistant_professor_12_month',
                        'ft_other_covered_assistant_professor_12_month',
                        'ft_other_expenditure_instructor_12_month',
                        'ft_other_covered_instructor_12_month',
                        'ft_other_expenditure_lecturer_12_month',
                        'ft_other_covered_lecturer_12_month',
                        'ft_other_expenditure_no_rank_12_month',
                        'ft_other_covered_no_rank_12_month',
                    ),
                    '[total_row_12]',
                    'Section 3.   9-Month plus 12-Month converted**  (Calculates automatically)'
                ),
            )
        ),
        '4b' => array(
            // Faculty
            array(
                'rows' => array(
                    'Faculty on 9-Month Contracts (i.e., regardless of number of installments)',
                    'Retirement' => array(
                        'ft_retirement_expentirue_no_diff_9_month',
                        'ft_retirement_covered_no_diff_9_month'
                    ),
                    'Medical' => array(
                        'ft_medical_expentirue_no_diff_9_month',
                        'ft_medical_covered_no_diff_9_month'
                    ),
                    'Dental' => array(
                        'ft_dental_expentirue_no_diff_9_month',
                        'ft_dental_covered_no_diff_9_month'
                    ),
                    '(Optional) Combined Medical w/ Dental' => array(
                        'ft_combined_medical_dental_expentirue_no_diff_9_month',
                        'ft_combined_medical_dental_covered_no_diff_9_month'
                    ),
                    'Disability' => array(
                        'ft_disability_expentirue_no_diff_9_month',
                        'ft_disability_covered_no_diff_9_month'
                    ),
                    'Tuition' => array(
                        'ft_tuition_expentirue_no_diff_9_month',
                        'ft_tuition_covered_no_diff_9_month'
                    ),
                    'FICA' => array(
                        'ft_fica_expentirue_no_diff_9_month',
                        'ft_fica_covered_no_diff_9_month'
                    ),
                    'Unemployment' => array(
                        'ft_unemployment_expentirue_no_diff_9_month',
                        'ft_unemployment_covered_no_diff_9_month'
                    ),
                    'Group Life' => array(
                        'ft_group_life_expentirue_no_diff_9_month',
                        'ft_group_life_covered_no_diff_9_month'
                    ),
                    'Worker\'s Comp.' => array(
                        'ft_worker_comp_expentirue_no_diff_9_month',
                        'ft_worker_comp_covered_no_diff_9_month'
                    ),
                    'Other' => array(
                        'ft_other_expentirue_no_diff_9_month',
                        'ft_other_covered_no_diff_9_month'
                    ),
                    '[total_row_9]'

                ),
            ),
            array(
                'rows' => array(
                    'Faculty on 12-Month Contracts (i.e., on actual basis, no conversion)',
                    'Retirement' => array(
                        'ft_retirement_expentirue_no_diff_12_month',
                        'ft_retirement_covered_no_diff_12_month'
                    ),
                    'Medical' => array(
                        'ft_medical_expentirue_no_diff_12_month',
                        'ft_medical_covered_no_diff_12_month'
                    ),
                    'Dental' => array(
                        'ft_dental_expentirue_no_diff_12_month',
                        'ft_dental_covered_no_diff_12_month'
                    ),
                    '(Optional) Combined Medical w/ Dental' => array(
                        'ft_combined_medical_dental_expentirue_no_diff_12_month',
                        'ft_combined_medical_dental_covered_no_diff_12_month'
                    ),
                    'Disability' => array(
                        'ft_disability_expentirue_no_diff_12_month',
                        'ft_disability_covered_no_diff_12_month'
                    ),
                    'Tuition' => array(
                        'ft_tuition_expentirue_no_diff_12_month',
                        'ft_tuition_covered_no_diff_12_month'
                    ),
                    'FICA' => array(
                        'ft_fica_expentirue_no_diff_12_month',
                        'ft_fica_covered_no_diff_12_month'
                    ),
                    'Unemployment' => array(
                        'ft_unemployment_expentirue_no_diff_12_month',
                        'ft_unemployment_covered_no_diff_12_month'
                    ),
                    'Group Life' => array(
                        'ft_group_life_expentirue_no_diff_12_month',
                        'ft_group_life_covered_no_diff_12_month'
                    ),
                    'Worker\'s Comp.' => array(
                        'ft_worker_comp_expentirue_no_diff_12_month',
                        'ft_worker_comp_covered_no_diff_12_month'
                    ),
                    'Other' => array(
                        'ft_other_expentirue_no_diff_12_month',
                        'ft_other_covered_no_diff_12_month'
                    ),
                    '[total_row_12]',
                    'Section 3.   9-Month plus 12-Month converted**  (Calculates automatically)'
                ),
            )
        ),
        // Full-time continuing
        5 => array(
            array(
                'rows' => array(
                    'Full-time Faculty on 9-Month Contracts',
                    'Professor' => array(
                        'ft_number_continuing_professor_standard',
                        'ft_current_salary_professor_standard',
                        'ft_previous_salary_professor_standard',
                    ),
                    'Associate' => array(
                        'ft_number_continuing_associate_professor_standard',
                        'ft_current_salary_associate_professor_standard',
                        'ft_previous_salary_associate_professor_standard',
                    ),
                    'Assistant' => array(
                        'ft_number_continuing_assistant_professor_standard',
                        'ft_current_salary_assistant_professor_standard',
                        'ft_previous_salary_assistant_professor_standard',
                    ),
                    'Instructor' => array(
                        'ft_number_continuing_instructor_standard',
                        'ft_current_salary_instructor_standard',
                        'ft_previous_salary_instructor_standard',
                    ),
                    'Lecturer' => array(
                        'ft_number_continuing_lecturer_standard',
                        'ft_current_salary_lecturer_standard',
                        'ft_previous_salary_lecturer_standard',
                    ),
                    'No Rank' => array(
                        'ft_number_continuing_no_rank_standard',
                        'ft_current_salary_no_rank_standard',
                        'ft_previous_salary_no_rank_standard',
                    ),
                ),
            ),
            array(
                'rows' => array(
                    'Full-time Faculty on 12-Month Contracts',
                    'Professor' => array(
                        'ft_number_continuing_professor_12_month',
                        'ft_current_salary_professor_12_month',
                        'ft_previous_salary_professor_12_month',
                    ),
                    'Associate' => array(
                        'ft_number_continuing_associate_professor_12_month',
                        'ft_current_salary_associate_professor_12_month',
                        'ft_previous_salary_associate_professor_12_month',
                    ),
                    'Assistant' => array(
                        'ft_number_continuing_assistant_professor_12_month',
                        'ft_current_salary_assistant_professor_12_month',
                        'ft_previous_salary_assistant_professor_12_month',
                    ),
                    'Instructor' => array(
                        'ft_number_continuing_instructor_12_month',
                        'ft_current_salary_instructor_12_month',
                        'ft_previous_salary_instructor_12_month',
                    ),
                    'Lecturer' => array(
                        'ft_number_continuing_lecturer_12_month',
                        'ft_current_salary_lecturer_12_month',
                        'ft_previous_salary_lecturer_12_month',
                    ),
                    'No Rank' => array(
                        'ft_number_continuing_no_rank_12_month',
                        'ft_current_salary_no_rank_12_month',
                        'ft_previous_salary_no_rank_12_month',
                    ),
                ),
            ),
        ),
        // Administrative compensation
        6 => array(
            array(
                'rows' => array(
                    'President/Chancellor' => array(
                        'ft_president_salary',
                        'ft_president_supplemental',
                    ),
                    'Chief Academic Officer' => array(
                        'ft_chief_academic_salary',
                        'ft_chief_academic_supplemental',
                    ),
                    'Chief Financial Officer' => array(
                        'ft_chief_financial_salary',
                        'ft_chief_financial_supplemental',
                    ),
                    'Chief Development Officer' => array(
                        'ft_chief_development_salary',
                        'ft_chief_development_supplemental',
                    ),
                    'Chief Administrative Officer' => array(
                        'ft_chief_administrative_salary',
                        'ft_chief_administrative_supplemental',
                    ),
                    'Chief Counsel' => array(
                        'ft_chief_counsel_salary',
                        'ft_chief_counsel_supplemental'
                    ),
                    'Director of Enrollment Management' => array(
                        'ft_director_enrollment_management_salary',
                        'ft_director_enrollment_management_supplemental'
                    ),
                    'Director of Athletics' => array(
                        'ft_director_athletics_salary',
                        'ft_director_athletics_supplemental'
                    )
                ),
            ),
        ),
        7 => array(
            array(
                'rows' => array(
                    'Part-Time Faculty' => array(
                        'pt_male_faculty',
                        'pt_male_salaries',
                        'pt_female_faculty',
                        'pt_female_salaries',
                        //'pt_total_faculty',
                        //'pt_total_salaries',
                    ),
                    'Graduate Teaching Assistant' => array(
                        'pt_male_graduate_teaching',
                        'pt_male_graduate_teaching_salaries',
                        'pt_female_graduate_teaching',
                        'pt_female_graduate_teaching_salaries',
                        //'pt_total_graduate_teaching',
                        //'pt_total_graduate_teaching_salaries',
                    ),
                    'Part-Time Per Section Faculty' => array(
                        'pt_male_per_section_faculty',
                        'pt_male_per_section_salaries',
                        'pt_female_per_section_faculty',
                        'pt_female_per_section_salaries',
                        //'pt_total_graduate_teaching',
                        //'pt_total_graduate_teaching_salaries',
                    )
                )
            )
        )
    )
);

