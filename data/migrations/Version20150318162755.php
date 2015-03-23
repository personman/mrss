<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150318162755 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Change several MRSS fields from integer to float
        $this->addSql("update benchmarks set inputType = 'float' where benchmarkGroup_id in (37,38,39,40,41) and inputType = 'number';");

        $this->addSql('ALTER TABLE observations CHANGE part_time_credit_hours part_time_credit_hours DOUBLE PRECISION DEFAULT NULL, CHANGE full_time_credit_hours full_time_credit_hours DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_inst op_exp_inst DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_student_services op_exp_student_services DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_acad_supp op_exp_acad_supp DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_inst_support op_exp_inst_support DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_research op_exp_research DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_pub_serv op_exp_pub_serv DOUBLE PRECISION DEFAULT NULL, CHANGE op_exp_oper_n_maint op_exp_oper_n_maint DOUBLE PRECISION DEFAULT NULL, CHANGE inst_admin_num inst_admin_num DOUBLE PRECISION DEFAULT NULL, CHANGE inst_full_num inst_full_num DOUBLE PRECISION DEFAULT NULL, CHANGE inst_full_cred_hrs inst_full_cred_hrs DOUBLE PRECISION DEFAULT NULL, CHANGE inst_part_num inst_part_num DOUBLE PRECISION DEFAULT NULL, CHANGE inst_part_cred_hrs inst_part_cred_hrs DOUBLE PRECISION DEFAULT NULL, CHANGE inst_exec_num inst_exec_num DOUBLE PRECISION DEFAULT NULL, CHANGE ss_admiss_emp ss_admiss_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_recruitment_emp ss_recruitment_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_advising_emp ss_advising_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_advising_student ss_advising_student DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counseling_emp ss_counseling_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counseling_student ss_counseling_student DOUBLE PRECISION DEFAULT NULL, CHANGE ss_career_emp ss_career_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_career_student ss_career_student DOUBLE PRECISION DEFAULT NULL, CHANGE ss_financial_aid_emp ss_financial_aid_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_financial_aid_student ss_financial_aid_student DOUBLE PRECISION DEFAULT NULL, CHANGE ss_registrar_emp ss_registrar_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_tutoring_emp ss_tutoring_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_tutoring_students ss_tutoring_students DOUBLE PRECISION DEFAULT NULL, CHANGE ss_testing_emp ss_testing_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_testing_student ss_testing_student DOUBLE PRECISION DEFAULT NULL, CHANGE ss_cocurricular_emp ss_cocurricular_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_disabserv_emp ss_disabserv_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_disabserv_o_students ss_disabserv_o_students DOUBLE PRECISION DEFAULT NULL, CHANGE ss_vetserv_emp ss_vetserv_emp DOUBLE PRECISION DEFAULT NULL, CHANGE ss_vetserv_o_students ss_vetserv_o_students DOUBLE PRECISION DEFAULT NULL, CHANGE as_tech_emp as_tech_emp DOUBLE PRECISION DEFAULT NULL, CHANGE as_tech_student as_tech_student DOUBLE PRECISION DEFAULT NULL, CHANGE as_library_emp as_library_emp DOUBLE PRECISION DEFAULT NULL, CHANGE as_experiential_emp as_experiential_emp DOUBLE PRECISION DEFAULT NULL, CHANGE as_experiential_student as_experiential_student DOUBLE PRECISION DEFAULT NULL, CHANGE empl_satis_prep empl_satis_prep DOUBLE PRECISION DEFAULT NULL, CHANGE ft_f_yminus4_degr_and_transf ft_f_yminus4_degr_and_transf DOUBLE PRECISION DEFAULT NULL, CHANGE pt_f_yminus4_degr_and_transf pt_f_yminus4_degr_and_transf DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations CHANGE inst_full_num inst_full_num INT DEFAULT NULL, CHANGE inst_full_cred_hrs inst_full_cred_hrs INT DEFAULT NULL, CHANGE inst_part_num inst_part_num INT DEFAULT NULL, CHANGE inst_part_cred_hrs inst_part_cred_hrs INT DEFAULT NULL, CHANGE inst_exec_num inst_exec_num INT DEFAULT NULL, CHANGE inst_admin_num inst_admin_num INT DEFAULT NULL, CHANGE ss_admiss_emp ss_admiss_emp INT DEFAULT NULL, CHANGE ss_recruitment_emp ss_recruitment_emp INT DEFAULT NULL, CHANGE ss_advising_emp ss_advising_emp INT DEFAULT NULL, CHANGE ss_advising_student ss_advising_student INT DEFAULT NULL, CHANGE ss_counseling_emp ss_counseling_emp INT DEFAULT NULL, CHANGE ss_counseling_student ss_counseling_student INT DEFAULT NULL, CHANGE ss_career_emp ss_career_emp INT DEFAULT NULL, CHANGE ss_career_student ss_career_student INT DEFAULT NULL, CHANGE ss_financial_aid_emp ss_financial_aid_emp INT DEFAULT NULL, CHANGE ss_financial_aid_student ss_financial_aid_student INT DEFAULT NULL, CHANGE ss_registrar_emp ss_registrar_emp INT DEFAULT NULL, CHANGE ss_tutoring_emp ss_tutoring_emp INT DEFAULT NULL, CHANGE ss_tutoring_students ss_tutoring_students INT DEFAULT NULL, CHANGE ss_testing_emp ss_testing_emp INT DEFAULT NULL, CHANGE ss_testing_student ss_testing_student INT DEFAULT NULL, CHANGE ss_cocurricular_emp ss_cocurricular_emp INT DEFAULT NULL, CHANGE ss_disabserv_emp ss_disabserv_emp INT DEFAULT NULL, CHANGE ss_disabserv_o_students ss_disabserv_o_students INT DEFAULT NULL, CHANGE ss_vetserv_emp ss_vetserv_emp INT DEFAULT NULL, CHANGE ss_vetserv_o_students ss_vetserv_o_students INT DEFAULT NULL, CHANGE as_tech_emp as_tech_emp INT DEFAULT NULL, CHANGE as_tech_student as_tech_student INT DEFAULT NULL, CHANGE as_library_emp as_library_emp INT DEFAULT NULL, CHANGE as_experiential_emp as_experiential_emp INT DEFAULT NULL, CHANGE as_experiential_student as_experiential_student INT DEFAULT NULL, CHANGE part_time_credit_hours part_time_credit_hours INT DEFAULT NULL, CHANGE full_time_credit_hours full_time_credit_hours INT DEFAULT NULL, CHANGE op_exp_inst op_exp_inst INT DEFAULT NULL, CHANGE op_exp_student_services op_exp_student_services INT DEFAULT NULL, CHANGE op_exp_acad_supp op_exp_acad_supp INT DEFAULT NULL, CHANGE op_exp_inst_support op_exp_inst_support INT DEFAULT NULL, CHANGE op_exp_research op_exp_research INT DEFAULT NULL, CHANGE op_exp_pub_serv op_exp_pub_serv INT DEFAULT NULL, CHANGE op_exp_oper_n_maint op_exp_oper_n_maint INT DEFAULT NULL, CHANGE ft_f_yminus4_degr_and_transf ft_f_yminus4_degr_and_transf INT DEFAULT NULL, CHANGE pt_f_yminus4_degr_and_transf pt_f_yminus4_degr_and_transf INT DEFAULT NULL, CHANGE empl_satis_prep empl_satis_prep INT DEFAULT NULL');
    }
}
