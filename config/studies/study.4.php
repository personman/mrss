<?php

// AAUP - FCS
return array(
    'from_email' => 'jbarnshaw@aaup.org',
    //'cc_email' => 'jbarnshaw@aaup.org',
    'cc_email' => 'dfergu15@jccc.edu',
    //'approver_email' => 'jbarnshaw@aaup.org',
    'approver_email' => 'dfergu15@jccc.edu',
    'favicon' => '/images/aaup-favicon.ico',
    'breakpoints' => '20,40,60,80',
    'layout' => 'nccbp.phtml',
    'navigation' => 'fcs_navigation',
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
        4 => 'fcs/full-time-benefits.phtml',
        5 => 'fcs/full-time-continuing.phtml',
        6 => 'fcs/administrative.phtml',
        7 => 'fcs/part-time.phtml'
    ),
    'export_sheet_names' => array(
        // benchmarkGroup id => excel sheet name
        3 => array(
            'sheetName' => 'Form 2',
            'sectionStartingCells' => array(
                0 => 'B7',
                1 => 'G7',
                2 => 'B15',
                3 => 'G15',
            ),
            'extra' => array(
                //'institution_conversion_factor' => 'G14'
            )
        ),
        4 => array(
            'sheetName' => 'Form 3',
            'sectionStartingCells' => array(
                0 => 'B6',
                1 => 'B19'
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
                        'ft_retirement_expentirue_no_diff_9_month',
                        'ft_retirement_covered_no_diff_9_month'
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
                        'ft_medical_expentirue_no_diff_9_month',
                        'ft_medical_covered_no_diff_9_month'
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
                        'ft_dental_expentirue_no_diff_9_month',
                        'ft_dental_covered_no_diff_9_month'
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
                        'ft_combined_medical_dental_expentirue_no_diff_9_month',
                        'ft_combined_medical_dental_covered_no_diff_9_month'
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
                        'ft_disability_expentirue_no_diff_9_month',
                        'ft_disability_covered_no_diff_9_month'
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
                        'ft_tuition_expentirue_no_diff_9_month',
                        'ft_tuition_covered_no_diff_9_month'
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
                        'ft_fica_expentirue_no_diff_9_month',
                        'ft_fica_covered_no_diff_9_month'
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
                        'ft_unemployment_expentirue_no_diff_9_month',
                        'ft_unemployment_covered_no_diff_9_month'
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
                        'ft_group_life_expentirue_no_diff_9_month',
                        'ft_group_life_covered_no_diff_9_month'
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
                        'ft_worker_comp_expentirue_no_diff_9_month',
                        'ft_worker_comp_covered_no_diff_9_month'
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
                        'ft_retirement_expentirue_no_diff_12_month',
                        'ft_retirement_covered_no_diff_12_month'
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
                        'ft_medical_expentirue_no_diff_12_month',
                        'ft_medical_covered_no_diff_12_month'
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
                        'ft_dental_expentirue_no_diff_12_month',
                        'ft_dental_covered_no_diff_12_month'
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
                        'ft_combined_medical_dental_expentirue_no_diff_12_month',
                        'ft_combined_medical_dental_covered_no_diff_12_month'
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
                        'ft_disability_expentirue_no_diff_12_month',
                        'ft_disability_covered_no_diff_12_month'
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
                        'ft_tuition_expentirue_no_diff_12_month',
                        'ft_tuition_covered_no_diff_12_month'
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
                        'ft_fica_expentirue_no_diff_12_month',
                        'ft_fica_covered_no_diff_12_month'
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
                        'ft_unemployment_expentirue_no_diff_12_month',
                        'ft_unemployment_covered_no_diff_12_month'
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
                        'ft_group_life_expentirue_no_diff_12_month',
                        'ft_group_life_covered_no_diff_12_month'
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
                        'ft_worker_comp_expentirue_no_diff_12_month',
                        'ft_worker_comp_covered_no_diff_12_month'
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
                    'Section 1.  Faculty on 9-Month Contracts (i.e., regardless of number of salary installments)',
                    'Part-time Faculty' => array(
                        'pt_male_faculty',
                        'pt_male_salaries',
                        'pt_female_faculty',
                        'pt_female_salaries',
                        'pt_total_faculty',
                        'pt_total_salaries',
                    ),
                    'Graduate Teaching Assistant' => array(
                        'pt_male_graduate_teaching',
                        'pt_male_graduate_teaching_salaries',
                        'pt_female_graduate_teaching',
                        'pt_female_graduate_teaching_salaries',
                        'pt_total_graduate_teaching',
                        'pt_total_graduate_teaching_salaries',
                    )
                )
            )
        )
    )
);

