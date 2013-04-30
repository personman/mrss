<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416223353 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD grads_comp VARCHAR(20) DEFAULT NULL, ADD leave_noncomp VARCHAR(20) DEFAULT NULL, ADD tot_grad_abcpdfw VARCHAR(20) DEFAULT NULL, ADD tot_grad_abcpdf VARCHAR(20) DEFAULT NULL, ADD tot_grad_abcp VARCHAR(20) DEFAULT NULL, ADD ret_rate_value VARCHAR(20) DEFAULT NULL, ADD enr_succ_value VARCHAR(20) DEFAULT NULL, ADD comp_succ_value VARCHAR(20) DEFAULT NULL, ADD m_tot_grad_dev_rem_value VARCHAR(20) DEFAULT NULL, ADD m_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD m_abcp_value VARCHAR(20) DEFAULT NULL, ADD w_tot_grad_dev_rem_value VARCHAR(20) DEFAULT NULL, ADD w_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD w_abcp_value VARCHAR(20) DEFAULT NULL, ADD rw_tot_grad_dev_rem_value VARCHAR(20) DEFAULT NULL, ADD rw_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD rw_abcp_value VARCHAR(20) DEFAULT NULL, ADD r_tot_grad_dev_rem_value VARCHAR(20) DEFAULT NULL, ADD r_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD r_abcp_value VARCHAR(20) DEFAULT NULL, ADD m_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD w_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD rw_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD r_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD m_enr_succ_value VARCHAR(20) DEFAULT NULL, ADD w_enr_succ_value VARCHAR(20) DEFAULT NULL, ADD rw_enr_succ_value VARCHAR(20) DEFAULT NULL, ADD r_enr_succ_value VARCHAR(20) DEFAULT NULL, ADD m_comp_succ_value VARCHAR(20) DEFAULT NULL, ADD w_comp_succ_value VARCHAR(20) DEFAULT NULL, ADD rw_comp_succ_value VARCHAR(20) DEFAULT NULL, ADD r_comp_succ_value VARCHAR(20) DEFAULT NULL, ADD m_tot_abcp_hld_value VARCHAR(20) DEFAULT NULL, ADD w_tot_abcp_hld_value VARCHAR(20) DEFAULT NULL, ADD m_enr_coll_cour_value VARCHAR(20) DEFAULT NULL, ADD w_enr_coll_cour_value VARCHAR(20) DEFAULT NULL, ADD m_compl_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD w_compl_abcpdf_value VARCHAR(20) DEFAULT NULL, ADD m_compl_abcp_value VARCHAR(20) DEFAULT NULL, ADD w_compl_abcp_value VARCHAR(20) DEFAULT NULL, ADD m_coll_lev_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD w_coll_lev_ret_rate_value VARCHAR(20) DEFAULT NULL, ADD m_coll_lev_enr_rate_value VARCHAR(20) DEFAULT NULL, ADD w_coll_lev_enr_rate_value VARCHAR(20) DEFAULT NULL, ADD m_coll_lev_compl_rate_value VARCHAR(20) DEFAULT NULL, ADD w_coll_lev_compl_rate_value VARCHAR(20) DEFAULT NULL, ADD tot_compl_value VARCHAR(20) DEFAULT NULL, ADD no_tot_empl_rel_value VARCHAR(20) DEFAULT NULL, ADD no_tot_purs_edu_value VARCHAR(20) DEFAULT NULL, ADD tot_resp_empl_value VARCHAR(20) DEFAULT NULL, ADD no_tot_purs_edu_perc_value VARCHAR(20) DEFAULT NULL, ADD no_tot_emp_rel_perc_value VARCHAR(20) DEFAULT NULL, ADD emp_satis_prep_perc_value VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP grads_comp, DROP leave_noncomp, DROP tot_grad_abcpdfw, DROP tot_grad_abcpdf, DROP tot_grad_abcp, DROP ret_rate_value, DROP enr_succ_value, DROP comp_succ_value, DROP m_tot_grad_dev_rem_value, DROP m_abcpdf_value, DROP m_abcp_value, DROP w_tot_grad_dev_rem_value, DROP w_abcpdf_value, DROP w_abcp_value, DROP rw_tot_grad_dev_rem_value, DROP rw_abcpdf_value, DROP rw_abcp_value, DROP r_tot_grad_dev_rem_value, DROP r_abcpdf_value, DROP r_abcp_value, DROP m_ret_rate_value, DROP w_ret_rate_value, DROP rw_ret_rate_value, DROP r_ret_rate_value, DROP m_enr_succ_value, DROP w_enr_succ_value, DROP rw_enr_succ_value, DROP r_enr_succ_value, DROP m_comp_succ_value, DROP w_comp_succ_value, DROP rw_comp_succ_value, DROP r_comp_succ_value, DROP m_tot_abcp_hld_value, DROP w_tot_abcp_hld_value, DROP m_enr_coll_cour_value, DROP w_enr_coll_cour_value, DROP m_compl_abcpdf_value, DROP w_compl_abcpdf_value, DROP m_compl_abcp_value, DROP w_compl_abcp_value, DROP m_coll_lev_ret_rate_value, DROP w_coll_lev_ret_rate_value, DROP m_coll_lev_enr_rate_value, DROP w_coll_lev_enr_rate_value, DROP m_coll_lev_compl_rate_value, DROP w_coll_lev_compl_rate_value, DROP tot_compl_value, DROP no_tot_empl_rel_value, DROP no_tot_purs_edu_value, DROP tot_resp_empl_value, DROP no_tot_purs_edu_perc_value, DROP no_tot_emp_rel_perc_value, DROP emp_satis_prep_perc_value");
    }
}
