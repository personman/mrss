<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130430154938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ec_i_abcpdfw INT DEFAULT NULL, ADD ec_ii_abcpdfw INT DEFAULT NULL, ADD al_abcpdfw INT DEFAULT NULL, ADD sp_abcpdfw INT DEFAULT NULL, ADD ec_i_abcpdf INT DEFAULT NULL, ADD ec_ii_abcpdf INT DEFAULT NULL, ADD al_abcpdf INT DEFAULT NULL, ADD sp_abcpdf INT DEFAULT NULL, ADD ec_i_abcp INT DEFAULT NULL, ADD ec_ii_abcp INT DEFAULT NULL, ADD sp_abcp INT DEFAULT NULL, ADD ec_i_retention_rate DOUBLE PRECISION DEFAULT NULL, ADD ec_i_enr_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD ec_i_comp_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD ec_ii_retention_rate DOUBLE PRECISION DEFAULT NULL, ADD ec_ii_enr_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD ec_ii_comp_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD al_retention_rate DOUBLE PRECISION DEFAULT NULL, ADD al_enr_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD al_comp_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD sp_retention_rate DOUBLE PRECISION DEFAULT NULL, ADD sp_enr_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD sp_comp_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD a INT DEFAULT NULL, ADD b INT DEFAULT NULL, ADD c INT DEFAULT NULL, ADD d INT DEFAULT NULL, ADD f INT DEFAULT NULL, ADD p INT DEFAULT NULL, ADD w INT DEFAULT NULL, ADD total INT DEFAULT NULL, ADD a_perc DOUBLE PRECISION DEFAULT NULL, ADD b_perc DOUBLE PRECISION DEFAULT NULL, ADD c_perc DOUBLE PRECISION DEFAULT NULL, ADD p_perc DOUBLE PRECISION DEFAULT NULL, ADD d_perc DOUBLE PRECISION DEFAULT NULL, ADD f_perc DOUBLE PRECISION DEFAULT NULL, ADD w_perc DOUBLE PRECISION DEFAULT NULL, ADD withdrawal DOUBLE PRECISION DEFAULT NULL, ADD completed DOUBLE PRECISION DEFAULT NULL, ADD compl_succ DOUBLE PRECISION DEFAULT NULL, ADD anb DOUBLE PRECISION DEFAULT NULL, ADD serv_ar_min DOUBLE PRECISION DEFAULT NULL, ADD empl_tot_inst_min_pop INT DEFAULT NULL, ADD stud_tot_inst_min_pop INT DEFAULT NULL, ADD stud_inst_pop INT DEFAULT NULL, ADD perc_inst_min DOUBLE PRECISION DEFAULT NULL, ADD perc_inst_min_empl DOUBLE PRECISION DEFAULT NULL, ADD stud_inst_serv_ratio DOUBLE PRECISION DEFAULT NULL, ADD empl_inst_serv_ratio DOUBLE PRECISION DEFAULT NULL, ADD pub_hs_spr_hs_grad INT DEFAULT NULL, ADD pub_hs_fall INT DEFAULT NULL, ADD priv_hs_spr_hs_grad INT DEFAULT NULL, ADD priv_hs_fall INT DEFAULT NULL, ADD tot_hs_spr_hs_grad INT DEFAULT NULL, ADD tot_tot_hs_fall INT DEFAULT NULL, ADD pub_perc_enr DOUBLE PRECISION DEFAULT NULL, ADD priv_perc_enr DOUBLE PRECISION DEFAULT NULL, ADD tot_perc_enr DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ec_i_abcpdfw, DROP ec_ii_abcpdfw, DROP al_abcpdfw, DROP sp_abcpdfw, DROP ec_i_abcpdf, DROP ec_ii_abcpdf, DROP al_abcpdf, DROP sp_abcpdf, DROP ec_i_abcp, DROP ec_ii_abcp, DROP sp_abcp, DROP ec_i_retention_rate, DROP ec_i_enr_suc_rate, DROP ec_i_comp_suc_rate, DROP ec_ii_retention_rate, DROP ec_ii_enr_suc_rate, DROP ec_ii_comp_suc_rate, DROP al_retention_rate, DROP al_enr_suc_rate, DROP al_comp_suc_rate, DROP sp_retention_rate, DROP sp_enr_suc_rate, DROP sp_comp_suc_rate, DROP a, DROP b, DROP c, DROP d, DROP f, DROP p, DROP w, DROP total, DROP a_perc, DROP b_perc, DROP c_perc, DROP p_perc, DROP d_perc, DROP f_perc, DROP w_perc, DROP withdrawal, DROP completed, DROP compl_succ, DROP anb, DROP serv_ar_min, DROP empl_tot_inst_min_pop, DROP stud_tot_inst_min_pop, DROP stud_inst_pop, DROP perc_inst_min, DROP perc_inst_min_empl, DROP stud_inst_serv_ratio, DROP empl_inst_serv_ratio, DROP pub_hs_spr_hs_grad, DROP pub_hs_fall, DROP priv_hs_spr_hs_grad, DROP priv_hs_fall, DROP tot_hs_spr_hs_grad, DROP tot_tot_hs_fall, DROP pub_perc_enr, DROP priv_perc_enr, DROP tot_perc_enr");
    }
}
