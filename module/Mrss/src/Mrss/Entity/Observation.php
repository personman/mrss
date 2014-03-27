<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\Exception;
use Zend\Debug\Debug;

/** @ORM\Entity
 * @ORM\Table(name="observations")
 */
class Observation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $year;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $cipCode;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="observations")
     */
    protected $college;

    // MRSS
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_course_planning;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counceling;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_total_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_total_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_other_total_expend;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $inst_full_total_num;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $inst_part_total_num;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $inst_other_total_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_other;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_other;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_othr_other;

    /** @ORM\Column(type="string", length=200, nullable=true) */
    protected $inst_full_other_specify;

    /** @ORM\Column(type="string", length=200, nullable=true) */
    protected $inst_part_other_specify;

    /** @ORM\Column(type="string", length=200, nullable=true) */
    protected $inst_othr_other_specify;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_other;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counceling_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counceling_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_contract;

    /** @ORM\Column(type="string", nullable=true) */
    protected $male_cred_stud;

    /** @ORM\Column(type="string", nullable=true) */
    protected $first_gen_students;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_control;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_type;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $part_time_credit_hours;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $full_time_credit_hours;

    /** @ORM\Column(type="float", nullable=true) */
    protected $full_time_credit_hours_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $part_time_credit_hours_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_dev;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_online;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_face_to_face;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_hybrid;

    /** @ORM\Column(type="string", nullable=true) */
    protected $Instructional_support;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_inst;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_student_services;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_acad_supp;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_inst_support;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_research;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_pub_serv;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $op_exp_oper_n_maint;



    // NCCBP form 1:
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $unemp_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $med_hhold_inc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_cr_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_cr_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $trans_cred;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $t_c_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dev_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $crd_stud_minc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fem_cred_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_res_alien;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $blk_n_hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ind_alaska;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $asia_pacif;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $wht_n_hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $race_eth_unk;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tuition_fees;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $unre_o_rev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $loc_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $state_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tuition_fees_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $total_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ipeds_enr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_cr_hdct;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_res_alien_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $race_eth_unk_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hisp_anyrace_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ind_alaska_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $asian_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $blk_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $haw_pacific_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $white_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $two_or_more;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hs_stud_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pell_grant_rec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hs_stud_hdct;


    // NCCBP form 2

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_degr_cert;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_comp_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_degr_cert;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_comp_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $f_yminus7_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus7_degr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_minus7perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $percminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $percminus7_comtran;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_fminus7_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_yminus7_degr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_yminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perminus7_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_percminus7_tran;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_pminus7_comtran;


    // NCCBP form 3
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_stud_trans;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fst_yr_gpa;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fst_yr_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enro_next_yr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $avrg_1y_crh;

    // NCCBP form 4

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cr_st;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grad_bef_spr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_bef_spr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_fall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grad_bef_fall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $next_term_pers;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fall_fall_pers;

    // NCCBP form 5

    // Class properties cannot begin with a number, so prepend an 'n'
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n96_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n97_ova_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n98_enr_again;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ac_adv_coun;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ac_serv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $adm_fin_aid;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $camp_clim;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $camp_supp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $conc_indiv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $instr_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $reg_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $resp_div_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $safe_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $serv_exc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_centr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $act_coll_learn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $acad_chall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_fac_int;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $sup_learn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $choo_again;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ova_impr;

    // NCCBP form 6
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grads_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $leave_noncomp;

    // NCCBP form 7
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcpdfw;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $comp_succ;

    // NCCBP form 8
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_comp_succ;

    // NCCBP form 9
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_tot_abcp_hld;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_tot_abcp_hld;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_enr_coll_cour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_enr_coll_cour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_compl_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_compl_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_compl_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_compl_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_enr_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_enr_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_compl_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_compl_rate;


    // NCCBP form 10
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_compl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_empl_rel;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_purs_edu;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_resp_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_purs_edu_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_emp_rel_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $emp_satis_prep_perc;

    // NCCBP form 11

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $al_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $al_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcp;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_comp_suc_rate;


    // NCCBP form 12

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $a;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $b;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $c;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $d;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $f;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $p;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $w;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $a_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $b_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $c_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $p_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $d_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $f_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $w_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $withdrawal;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $completed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $compl_succ;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $group_form12_instw_cred_grad_enr_succ;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $anb;


    // NCCBP form 13a

    protected $empl_inst_pop;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $serv_ar_min;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $empl_tot_inst_min_pop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $stud_tot_inst_min_pop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $stud_inst_pop;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $perc_inst_min;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $perc_inst_min_empl;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $stud_inst_serv_ratio;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $empl_inst_serv_ratio;


    // NCCBP form 13b:

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pub_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pub_hs_fall;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $priv_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $priv_hs_fall;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tot_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tot_tot_hs_fall;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $pub_perc_enr;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $priv_perc_enr;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $tot_perc_enr;


    // Form 14a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $undup_cre_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $undup_non_cre_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $serv_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cre_stud_pen_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ncre_stud_pen_rate;


    // Form 14b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cul_act_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pub_meet_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $spo_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form14b_serv_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cul_com_part;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pub_com_part;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $spo_com_part;



    // Form 15
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fy_dup_headc_bni;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $comp_serv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_inst_adm_cst;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_rev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $net_revenue_usd;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $net_revenue_perc;



    // Form 16a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_cou_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $av_cred_sec_size;


    // Form 16c
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_stud_crhrs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_cred_sec_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_fac;


    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_stud_crhrs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_cred_sec_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_hrs;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_sec;


    // Form 17a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dis_lear_stud_hrs;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dis_lear_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_crh_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_crs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dist_prop_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dist_prop_crs;


    // Form 17b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_a;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_b;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_c;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_d;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_p;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_f;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_w;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_total;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_a_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_b_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_c_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_d_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_p_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_f_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_w_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_withdrawal;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_completed;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $completer_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $group_form17b_anb;



    // NCCBP form 18:
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_undup_cr_hd;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_career_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_counc_adv_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_recr_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_fin_aid_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_stud_act_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_test_ass_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $career_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $couns_adv_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $recr_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fin_aid_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_act_staff_ratio;






    // Form 19a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_ft_reg_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dep;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret_occ_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dep_occ_rate;



    // Form 19b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $griev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $harass;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $griev_occ_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $harass_occ_rate;




    // Form 20a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dir_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fy_stud_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cst_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cst_fte_stud;




    // Form 20b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dev_train_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_cred_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $exp_fte_empl;




    // NCCWTP
    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_duplicated_enrollment;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_unduplicated_enrollment;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_organizations_served;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_training_contracts;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_total_contact_hours;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $retention_returning_organizations;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $retention_returning_students;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_full_time_instructors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_instructors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_independent_contractors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_full_time_support_staff;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_support_staff;

    /** @ORM\Column(type="float", nullable=true) */
    protected $transition_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_federal;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_state;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_local;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_grants;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_earned_revenue;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_benefits;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_supplies;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_marketing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_capital_equipment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_travel;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_overhead;

    /** @ORM\Column(type="string", nullable=true) */
    protected $retained_revenue_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_roi;

    /** @ORM\Column(type="float", nullable=true) */
    protected $satisfaction_client;

    /** @ORM\Column(type="float", nullable=true) */
    protected $satisfaction_student;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_credit_enrollment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_operating_revenue;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_campus_environment;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_faculty_unionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_staff_unionized;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_total_population;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_total_companies;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_less_than_50;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_50_to_99;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_100_to_499;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_500_or_greater;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_unemployment_rate;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_median_household_income;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_credentials_awarded;

    /** @ORM\Column(type="float", nullable=true) */
    protected $enrollment_information_workforce_enrollment_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $enrollment_information_market_penetration;

    /** @ORM\Column(type="string", nullable=true) */
    protected $enrollment_information_contact_hours_per_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retention_percent_returning_organizations_served;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retention_percent_returning_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $staffing_full_time_instructors_percent;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_instructors_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $staffing_independent_contractors_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $staffing_instructor_staff_ratio;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_contract_training_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_continuing_education_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_salaries_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_benefits_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_supplies_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_marketing_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_capital_equipment_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_travel_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_contract_training_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_continuing_education_percent;



    // Test fields
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $test_ass_staff_ratio;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $test_student_count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $test_green_eye_count;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $test_green_eye_percentage;









    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function setCipCode($cipCode)
    {
        $this->cipCode = $cipCode;

        return $this;
    }

    public function getCipCode()
    {
        return $this->cipCode;
    }

    public function has($benchmark)
    {
        return property_exists($this, $benchmark);
    }


    /**
     * @param $benchmark
     * @return mixed
     * @throws Exception\InvalidBenchmarkException
     */
    public function get($benchmark)
    {
        if (!$this->has($benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        return $this->$benchmark;
    }

    public function set($benchmark, $value)
    {
        if (!property_exists($this, $benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        // Convert empty strings to null so they don't end up as 0
        if ($value === '') {
            $value = null;
        }

        $this->$benchmark = $value;

        return $this;
    }

    public function getArrayCopy()
    {
        $arrayCopy = array();
        foreach ($this as $key => $value) {
            $arrayCopy[$key] = $value;
        }

        return $arrayCopy;
    }

    /**
     * Hydrator method for putting form values into entity
     *
     * @param array $observationArray
     */
    public function populate($observationArray)
    {
        foreach ($observationArray as $key => $value) {
            if ($this->has($key)) {
                $this->set($key, $value);
            }
        }
    }
}
