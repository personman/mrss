<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150402155623 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD max_res_institutional_demographics_unemployment_rate DOUBLE PRECISION DEFAULT NULL, ADD max_res_institutional_demographics_median_household_income DOUBLE PRECISION DEFAULT NULL, ADD max_res_institutional_demographics_faculty_unionized VARCHAR(255) DEFAULT NULL, ADD max_res_institutional_demographics_staff_unionized VARCHAR(255) DEFAULT NULL, ADD max_res_institutional_demographics_campus_environment VARCHAR(255) DEFAULT NULL, ADD max_res_ipeds_enr DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_cr_head DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_cr_head DOUBLE PRECISION DEFAULT NULL, ADD max_res_hs_stud_hdct DOUBLE PRECISION DEFAULT NULL, ADD max_res_hs_stud_crh DOUBLE PRECISION DEFAULT NULL, ADD max_res_pell_grant_rec DOUBLE PRECISION DEFAULT NULL, ADD max_res_male_cred_stud DOUBLE PRECISION DEFAULT NULL, ADD max_res_fem_cred_stud DOUBLE PRECISION DEFAULT NULL, ADD max_res_first_gen_students DOUBLE PRECISION DEFAULT NULL, ADD max_res_trans_cred DOUBLE PRECISION DEFAULT NULL, ADD max_res_t_c_crh DOUBLE PRECISION DEFAULT NULL, ADD max_res_dev_crh DOUBLE PRECISION DEFAULT NULL, ADD max_res_crd_stud_minc DOUBLE PRECISION DEFAULT NULL, ADD max_res_non_res_alien_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_hisp_anyrace_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_ind_alaska_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_asian_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_blk_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_haw_pacific_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_white_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_two_or_more DOUBLE PRECISION DEFAULT NULL, ADD max_res_race_eth_unk_2012 DOUBLE PRECISION DEFAULT NULL, ADD max_res_tuition_fees DOUBLE PRECISION DEFAULT NULL, ADD max_res_unre_o_rev DOUBLE PRECISION DEFAULT NULL, ADD max_res_loc_sour DOUBLE PRECISION DEFAULT NULL, ADD max_res_state_sour DOUBLE PRECISION DEFAULT NULL, ADD max_res_tuition_fees_sour DOUBLE PRECISION DEFAULT NULL, ADD max_res_institutional_control VARCHAR(255) DEFAULT NULL, ADD max_res_institutional_type VARCHAR(255) DEFAULT NULL, ADD max_res_tot_fy_stud_crh DOUBLE PRECISION DEFAULT NULL, ADD max_res_enrollment_information_duplicated_enrollment DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_tot_stud_crhrs_tght DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_tot_stud_crhrs_tght DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_f_yminus4_headc INT DEFAULT NULL, ADD max_res_op_exp_inst DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_f_yminus4_degr_cert INT DEFAULT NULL, ADD max_res_op_exp_student_services DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_f_yminus4_transf INT DEFAULT NULL, ADD max_res_op_exp_acad_supp DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_f_yminus4_headc INT DEFAULT NULL, ADD max_res_op_exp_inst_support DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_f_yminus4_degr_cert INT DEFAULT NULL, ADD max_res_op_exp_research DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_f_yminus4_transf INT DEFAULT NULL, ADD max_res_op_exp_pub_serv DOUBLE PRECISION DEFAULT NULL, ADD max_res_f_yminus7_headc INT DEFAULT NULL, ADD max_res_op_exp_oper_n_maint DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_yminus7_degr INT DEFAULT NULL, ADD max_res_ft_yminus7_transf INT DEFAULT NULL, ADD max_res_pt_fminus7_headc INT DEFAULT NULL, ADD max_res_pt_yminus7_degr INT DEFAULT NULL, ADD max_res_pt_yminus7_transf INT DEFAULT NULL, ADD max_res_tot_grad_abcpdfw INT DEFAULT NULL, ADD max_res_tot_grad_abcpdf INT DEFAULT NULL, ADD max_res_tot_grad_abcp INT DEFAULT NULL, ADD max_res_tot_cr_st INT DEFAULT NULL, ADD max_res_grad_bef_spr INT DEFAULT NULL, ADD max_res_enr_bef_spr INT DEFAULT NULL, ADD max_res_grad_bef_fall INT DEFAULT NULL, ADD max_res_enr_fall INT DEFAULT NULL, ADD max_res_fall_fall_pers DOUBLE PRECISION DEFAULT NULL, ADD max_res_next_term_pers DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP max_res_institutional_demographics_unemployment_rate, DROP max_res_institutional_demographics_median_household_income, DROP max_res_institutional_demographics_faculty_unionized, DROP max_res_institutional_demographics_staff_unionized, DROP max_res_institutional_demographics_campus_environment, DROP max_res_ipeds_enr, DROP max_res_ft_cr_head, DROP max_res_pt_cr_head, DROP max_res_hs_stud_hdct, DROP max_res_hs_stud_crh, DROP max_res_pell_grant_rec, DROP max_res_male_cred_stud, DROP max_res_fem_cred_stud, DROP max_res_first_gen_students, DROP max_res_trans_cred, DROP max_res_t_c_crh, DROP max_res_dev_crh, DROP max_res_crd_stud_minc, DROP max_res_non_res_alien_2012, DROP max_res_hisp_anyrace_2012, DROP max_res_ind_alaska_2012, DROP max_res_asian_2012, DROP max_res_blk_2012, DROP max_res_haw_pacific_2012, DROP max_res_white_2012, DROP max_res_two_or_more, DROP max_res_race_eth_unk_2012, DROP max_res_tuition_fees, DROP max_res_unre_o_rev, DROP max_res_loc_sour, DROP max_res_state_sour, DROP max_res_tuition_fees_sour, DROP max_res_institutional_control, DROP max_res_institutional_type, DROP max_res_tot_fy_stud_crh, DROP max_res_enrollment_information_duplicated_enrollment, DROP max_res_ft_tot_stud_crhrs_tght, DROP max_res_pt_tot_stud_crhrs_tght, DROP max_res_ft_f_yminus4_headc, DROP max_res_op_exp_inst, DROP max_res_ft_f_yminus4_degr_cert, DROP max_res_op_exp_student_services, DROP max_res_ft_f_yminus4_transf, DROP max_res_op_exp_acad_supp, DROP max_res_pt_f_yminus4_headc, DROP max_res_op_exp_inst_support, DROP max_res_pt_f_yminus4_degr_cert, DROP max_res_op_exp_research, DROP max_res_pt_f_yminus4_transf, DROP max_res_op_exp_pub_serv, DROP max_res_f_yminus7_headc, DROP max_res_op_exp_oper_n_maint, DROP max_res_ft_yminus7_degr, DROP max_res_ft_yminus7_transf, DROP max_res_pt_fminus7_headc, DROP max_res_pt_yminus7_degr, DROP max_res_pt_yminus7_transf, DROP max_res_tot_grad_abcpdfw, DROP max_res_tot_grad_abcpdf, DROP max_res_tot_grad_abcp, DROP max_res_tot_cr_st, DROP max_res_grad_bef_spr, DROP max_res_enr_bef_spr, DROP max_res_grad_bef_fall, DROP max_res_enr_fall, DROP max_res_fall_fall_pers, DROP max_res_next_term_pers');
    }
}