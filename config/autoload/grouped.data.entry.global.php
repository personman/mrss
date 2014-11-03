<?php
/**
 * This is only needed for NCCBP data entry, so it probably shouldn't be auto loaded
 */

return array(
    'data-entry' => array(
        'grouped' => array(
           'form1_subscriber_info' => array(

               array(
                   'title' => 'Service Area',
                   'description' => 'Use legal definition of service area and most recent census estimates.',
                   'benchmarks' => array(
                       'total_pop',
                       'unemp_rate',
                       'med_hhold_inc',
                   ),
               ),

               array(
                   'title' => 'Enrollment Information',
                   'description' => 'Use fall [year_minus_2] data.',
                   'benchmarks' => array(
                       'ipeds_enr',
                       'ft_cr_head',
                       'pt_cr_head',
                       'hs_stud_hdct',
                       'pell_grant_rec',
                       'non_cr_hdct',
                       'fem_cred_stud',
                       'first_gen_students',
                       'trans_cred',
                       't_c_crh',
                       'dev_crh',
                       'hs_stud_crh',
                       'crd_stud_minc',
                   ),
               ),

               array(
                   'title' => 'Race/Ethnicity ',
                   'description' => 'Use Fall [year_minus_2] data.<br>
<br>
Race/ethnicity percentages should be IPEDS Fall Enrollment figures and total 100%. Please refer to new IPEDS race/ethnicity definitions.',
                   'benchmarks' => array(
                       'non_res_alien_2012',
                       'hisp_anyrace_2012',
                       'ind_alaska_2012',
                       'asian_2012',
                       'blk_2012',
                       'haw_pacific_2012',
                       'white_2012',
                       'two_or_more',
                       'race_eth_unk_2012',
                   ),
               ),

               array(
                   'title' => 'Fiscal Information',
                   'description' => '',
                   'benchmarks' => array(
                       'tuition_fees',
                       'unre_o_rev',
                   ),
               ),

               array(
                   'title' => 'Operating Revenue Sources',
                   'description' => 'Use fiscal year 2013 data.<br>
<br>
May not add up to 100%.',
                   'benchmarks' => array(
                       'loc_sour',
                       'state_sour',
                       'tuition_fees_sour',
                   ),
               ),
               array(
                   'title' => 'Campus Information',
                   'description' => '',
                   'benchmarks' => array(
                       'institutional_demographics_campus_type',
                       'institutional_demographics_campus_environment',
                       'institutional_demographics_faculty_unionized',
                       'institutional_demographics_staff_unionized',
                       'institutional_demographics_control',
                       'institutional_demographics_calendar'
                   ),
               ),
           ),
           'form2_student_compl_tsf' => array(

               array(
                   'title' => 'Full-time, first-time in fall [year_minus_4]',
                   'description' => '',
                   'benchmarks' => array(
                       'ft_f_yminus4_headc',
                       'ft_f_yminus4_degr_cert',
                       'ft_f_yminus4_transf',
                   ),
               ),

               array(
                   'title' => 'Part-time, first-time in fall [year_minus_4]',
                   'description' => '',
                   'benchmarks' => array(
                       'pt_f_yminus4_headc',
                       'pt_f_yminus4_degr_cert',
                       'pt_f_yminus4_transf',
                   ),
               ),

               array(
                   'title' => 'Full-time, first-time in fall [year_minus_7]',
                   'description' => '',
                   'benchmarks' => array(
                       'f_yminus7_headc',
                       'ft_yminus7_degr',
                       'ft_yminus7_transf',
                   ),
               ),

               array(
                   'title' => 'Part-time, first-time in fall [year_minus_7]',
                   'description' => '',
                   'benchmarks' => array(
                       'pt_fminus7_headc',
                       'pt_yminus7_degr',
                       'pt_yminus7_transf',
                   ),
               ),
           ),
            'form3_stu_perf_transf' => array(

                array(
                    'title' => 'At Two-Year Institution',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_stud_trans',
                    ),
                ),

                array(
                    'title' => 'At Four-Year Transfer Institutions',
                    'description' => '',
                    'benchmarks' => array(
                        'fst_yr_gpa',
                        'tot_fst_yr_crh',
                        'enro_next_yr',
                    ),
                ),
            ),
            'form4_cred_stud_enr' => array(

                array(
                    'title' => 'Fall [year_minus_2] Term',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_cr_st',
                    ),
                ),

                array(
                    'title' => 'Next Term',
                    'description' => '',
                    'benchmarks' => array(
                        'grad_bef_spr',
                        'enr_bef_spr',
                    ),
                ),

                array(
                    'title' => 'Next Fall',
                    'description' => '',
                    'benchmarks' => array(
                        'grad_bef_fall',
                        'enr_fall',
                    ),
                ),
            ),
            'form5_stud_satis_eng' => array(

                array(
                    'title' => 'Noel-Levitz Summary Items',
                    'description' => '',
                    'benchmarks' => array(
                        'n96_exp',
                        'n97_ova_exp',
                        'n98_enr_again',
                    ),
                ),

                array(
                    'title' => 'Noel-Levitz Scale Items',
                    'description' => 'Enter satisfaction means, not importance means or performance gaps.',
                    'benchmarks' => array(
                        'ac_adv_coun',
                        'ac_serv',
                        'adm_fin_aid',
                        'camp_clim',
                        'camp_supp',
                        'conc_indiv',
                        'instr_eff',
                        'reg_eff',
                        'resp_div_pop',
                        'safe_sec',
                        'serv_exc',
                        'stud_centr',
                    ),
                ),

                array(
                    'title' => 'CCSSE Summary Benchmarks',
                    'description' => 'CCSSE summary benchmark means are available in the Members Only section at the CCSSE website.',
                    'benchmarks' => array(
                        'act_coll_learn',
                        'stud_eff',
                        'acad_chall',
                        'stud_fac_int',
                        'sup_learn',
                    ),
                ),

                array(
                    'title' => 'ACT Student Opinion Survey',
                    'description' => '',
                    'benchmarks' => array(
                        'choo_again',
                        'ova_impr',
                    ),
                ),
            ),
            'form6_stud_goal' => array(

                array(
                    'title' => 'Did you achieve your educational objective?',
                    'description' => 'Enter the percent of graduates/program completers and leavers/non-completers that indicated they had achieved their educational objective either partially or fully.<br>
<br>
Data source will most likely be an exit survey or a follow-up survey administered soon after students leave the institution.',
                    'benchmarks' => array(
                        'grads_comp',
                        'leave_noncomp',
                    ),
                ),
            ),
            'form7_col_ret_succ' => array(

                array(
                    'title' => 'Fall Grades',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_grad_abcpdfw',
                        'tot_grad_abcpdf',
                        'tot_grad_abcp',
                    ),
                ),
            ),
            'form8_dev_ret_succ' => array(

                array(
                    'title' => 'Math',
                    'description' => '',
                    'benchmarks' => array(
                        'm_tot_grad_dev_rem',
                        'm_abcpdf',
                        'm_abcp',
                    ),
                ),

                array(
                    'title' => 'Writing',
                    'description' => '',
                    'benchmarks' => array(
                        'w_tot_grad_dev_rem',
                        'w_abcpdf',
                        'w_abcp',
                    ),
                ),

                array(
                    'title' => 'Reading / Writing',
                    'description' => '',
                    'benchmarks' => array(
                        'rw_tot_grad_dev_rem',
                        'rw_abcpdf',
                        'rw_abcp',
                    ),
                ),

                array(
                    'title' => 'Reading',
                    'description' => '',
                    'benchmarks' => array(
                        'r_tot_grad_dev_rem',
                        'r_abcpdf',
                        'r_abcp',
                    ),
                ),
            ),
            'form9_dev_ret_succ_first_c' => array(

                array(
                    'title' => 'Math',
                    'description' => '',
                    'benchmarks' => array(
                        'm_tot_abcp_hld',
                        'm_enr_coll_cour',
                        'm_compl_abcpdf',
                        'm_compl_abcp',
                    ),
                ),

                array(
                    'title' => 'Writing',
                    'description' => '',
                    'benchmarks' => array(
                        'w_tot_abcp_hld',
                        'w_enr_coll_cour',
                        'w_compl_abcpdf',
                        'w_compl_abcp',
                    ),
                ),
            ),
            'form10_career_comp' => array(

                array(
                    'title' => 'Related Field of Employment / Pursuing Education',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_compl',
                        'no_tot_empl_rel',
                        'no_tot_purs_edu',
                    ),
                ),

                array(
                    'title' => 'Employer Satisfaction',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_resp_empl',
                        'empl_satis_prep',
                    ),
                ),
            ),
            'form11_ret_succ_core' => array(

                array(
                    'title' => 'English Comp I',
                    'description' => '',
                    'benchmarks' => array(
                        'ec_i_abcpdfw',
                        'ec_i_abcpdf',
                        'ec_i_abcp',
                    ),
                ),

                array(
                    'title' => 'English Comp II',
                    'description' => '',
                    'benchmarks' => array(
                        'ec_ii_abcpdfw',
                        'ec_ii_abcpdf',
                        'ec_ii_abcp',
                    ),
                ),

                array(
                    'title' => 'College Algebra',
                    'description' => '',
                    'benchmarks' => array(
                        'al_abcpdfw',
                        'al_abcpdf',
                        'al_abcp',
                    ),
                ),

                array(
                    'title' => 'Speech',
                    'description' => '',
                    'benchmarks' => array(
                        'sp_abcpdfw',
                        'sp_abcpdf',
                        'sp_abcp',
                    ),
                ),
            ),
            'form12_instw_cred_grad' => array(

                array(
                    'title' => 'Fall Grades',
                    'description' => 'Enter the total number of A, B, C, P, D, F and W grades (or their institutional equivalents) at the end of the fall [year_minus_2] term.<br>
<br>
Include all other passing grades with P. Include all other non-passing grades with F. Include +\'s and -\'s in the letter grades with which they are associated (e.g. a grade of C+ would be reported with C grades). Do not include incompletes and audits.<br>
<br>
Include grades in credit distance learning classes and grades in credit developmental/remedial courses.

',
                    'benchmarks' => array(
                        'a',
                        'b',
                        'c',
                        'p',
                        'd',
                        'f',
                        'w',
                    ),
                ),
            ),
            'form13a_minority' => array(

                array(
                    'title' => 'Service Area',
                    'description' => 'Use most recent census estimates.',
                    'benchmarks' => array(
                        'serv_ar_min',
                    ),
                ),

                array(
                    'title' => 'Credit Students',
                    'description' => 'Use fall [year_minus_2] data.',
                    'benchmarks' => array(
                        'stud_inst_pop',
                        'stud_tot_inst_min_pop',
                    ),
                ),

                array(
                    'title' => 'Employees',
                    'description' => 'Use fall [year_minus_2] data.',
                    'benchmarks' => array(
                        'empl_inst_pop',
                        'empl_tot_inst_min_pop',
                    ),
                ),
            ),
            'form13b_hschool_grads' => array(

                array(
                    'title' => 'Public High Schools',
                    'description' => '',
                    'benchmarks' => array(
                        'pub_hs_spr_hs_grad',
                        'pub_hs_fall',
                    ),
                ),

                array(
                    'title' => 'Private High Schools',
                    'description' => '',
                    'benchmarks' => array(
                        'priv_hs_spr_hs_grad',
                        'priv_hs_fall',
                    ),
                ),

                array(
                    'title' => 'Total',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_hs_spr_hs_grad',
                        'tot_tot_hs_fall',
                    ),
                ),
            ),
            'form14a_market_pen_stud' => array(

                array(
                    'title' => 'Service Area Information',
                    'description' => '',
                    'benchmarks' => array(
                        'serv_pop',
                    ),
                ),

                array(
                    'title' => 'Credit Students',
                    'description' => 'Use AY [year_minus_2]-[year_minus_1] data.',
                    'benchmarks' => array(
                        'undup_cre_head',
                    ),
                ),

                array(
                    'title' => 'Non-Credit Students',
                    'description' => 'Use AY [year_minus_2]-[year_minus_1] data.',
                    'benchmarks' => array(
                        'undup_non_cre_head',
                    ),
                ),
            ),
            'form14b_market_pen_com' => array(

                array(
                    'title' => 'Service Area Information',
                    'description' => '',
                    'benchmarks' => array(
                        'serv_pop',
                    ),
                ),

                array(
                    'title' => 'Cultural Activities',
                    'description' => 'Use AY [year_minus_2]-[year_minus_1] data.',
                    'benchmarks' => array(
                        'cul_act_dupl_head',
                    ),
                ),

                array(
                    'title' => 'Public Meetings',
                    'description' => 'Use AY [year_minus_2]-[year_minus_1] data.',
                    'benchmarks' => array(
                        'pub_meet_dupl_head',
                    ),
                ),

                array(
                    'title' => 'Sporting Events',
                    'description' => 'Use AY [year_minus_2]-[year_minus_1] data.',
                    'benchmarks' => array(
                        'spo_dupl_head',
                    ),
                ),
            ),
            'form15_fy_bni' => array(

                array(
                    'title' => 'Services to the Community',
                    'description' => '',
                    'benchmarks' => array(
                        'fy_dup_headc_bni',
                        'comp_serv',
                    ),
                ),

                array(
                    'title' => 'Costs vs. Revenue',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_inst_adm_cst',
                        'tot_rev',
                    ),
                ),
            ),
            'form16a_av_cred_sect' => array(

                array(
                    'title' => 'Credit Course Sections',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_cred_cou_sec',
                    ),
                ),

                array(
                    'title' => 'Credit Students',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_cred_stud',
                    ),
                ),
            ),
            'form16b_cred_co_stud_fac' => array(

                array(
                    'title' => 'FTE Faculty',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_fte_fac',
                    ),
                ),

                array(
                    'title' => 'FTE Students',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_fte_stud',
                    ),
                ),
            ),
            'form16c_inst_fac_load' => array(

                array(
                    'title' => 'Full-time',
                    'description' => '',
                    'benchmarks' => array(
                        'ft_tot_fac',
                        'ft_tot_stud_crhrs_tght',
                        'ft_tot_cred_sec_tght',
                    ),
                ),

                array(
                    'title' => 'Part-time',
                    'description' => '',
                    'benchmarks' => array(
                        'pt_tot_fac',
                        'pt_tot_stud_crhrs_tght',
                        'pt_tot_cred_sec_tght',
                    ),
                ),
            ),
            'form17a_dist_lear_sec_cred' => array(

                array(
                    'title' => 'Credit Hours',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_crh_tght',
                        'tot_dis_lear_stud_hrs',
                    ),
                ),

                array(
                    'title' => 'Credit Sections',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_crs_tght',
                        'tot_dis_lear_sec',
                    ),
                ),
            ),
            'form17b_dist_learn_grad' => array(

                array(
                    'title' => 'Fall Grades',
                    'description' => 'Enter the total number of A, B, C, P, D, F, and W grades in credit distance learning courses at end of the fall [year_minus_2] term. If there were no students awarded a grade, enter zero (0).<br>
<br>
Include all other passing grades with P. Include all other non-passing grades with F. Include +\'s and -\'s in the letter grades with which they are associated (e.g. a grade of C+ would be reported with C grades). Do not include incompletes and audits.',
                    'benchmarks' => array(
                        'a',
                        'b',
                        'c',
                        'p',
                        'd',
                        'f',
                        'w',
                    ),
                ),
            ),
            'form18_stud_serv_staff' => array(

                array(
                    'title' => 'Credit Headcount',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_undup_cr_hd',
                    ),
                ),

                array(
                    'title' => 'Total FTE Professional Services Staff',
                    'description' => 'Enter the total FTE professional student services staff at the end of the FY [year_minus_2]-[year_minus_1].<br>
<br>
Professional Student Services Staff includes professional employees who provide non-instructional support services to students. Do not include clerical staff or athletic coaches, vice-presidents, deans, or their immediate staff, but do include directors in each area.<br>
<br>
Functional areas need not all be within a student services division.<br>
<br>
Figures should = (total number of full-time staff) + (total part-time, non-clerical staff hours / 2080) for each student services area. Schools with work weeks less than 40 hours should adjust yearly hour figure for part-time staff.',
                    'benchmarks' => array(
                        'tot_fte_career_staff',
                        'tot_fte_counc_adv_staff',
                        'tot_fte_recr_staff',
                        'tot_fte_fin_aid_staff',
                        'tot_fte_stud_act_staff',
                        'tot_fte_test_ass_staff',
                    ),
                ),
            ),
            'form19a_ret_dept' => array(

                array(
                    'title' => 'Full-time, Regular Employees',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_ft_reg_empl',
                    ),
                ),

                array(
                    'title' => 'Retirements',
                    'description' => '',
                    'benchmarks' => array(
                        'ret',
                    ),
                ),

                array(
                    'title' => 'Departures',
                    'description' => '',
                    'benchmarks' => array(
                        'dep',
                    ),
                ),
            ),
            'form19b_griev_har' => array(

                array(
                    'title' => 'Employees',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_empl',
                    ),
                ),

                array(
                    'title' => 'Grievances',
                    'description' => '',
                    'benchmarks' => array(
                        'griev',
                    ),
                ),

                array(
                    'title' => 'Harassment',
                    'description' => '',
                    'benchmarks' => array(
                        'harass',
                    ),
                ),
            ),
            'form20a_cst_crh_fte_stud' => array(

                array(
                    'title' => 'Expenditures',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_dir_exp',
                    ),
                ),

                array(
                    'title' => 'Students',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_fy_stud_crh',
                    ),
                ),
            ),
            'form20b_dev_train_per_empl' => array(

                array(
                    'title' => 'Expenditures',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_dev_train_exp',
                    ),
                ),

                array(
                    'title' => 'Faculty and Staff',
                    'description' => '',
                    'benchmarks' => array(
                        'tot_fte_cred_fac',
                        'tot_fte_staff',
                    ),
                ),
            )
        )
    )
);
