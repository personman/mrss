<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130603144822 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD fy_dup_headc_bni VARCHAR(20) DEFAULT NULL, ADD comp_serv VARCHAR(20) DEFAULT NULL, ADD tot_inst_adm_cst VARCHAR(20) DEFAULT NULL, ADD tot_rev VARCHAR(20) DEFAULT NULL, ADD net_revenue_usd VARCHAR(20) DEFAULT NULL, ADD net_revenue_perc VARCHAR(20) DEFAULT NULL, ADD tot_cred_cou_sec VARCHAR(20) DEFAULT NULL, ADD tot_cred_stud VARCHAR(20) DEFAULT NULL, ADD av_cred_sec_size VARCHAR(20) DEFAULT NULL, ADD ft_tot_fac VARCHAR(20) DEFAULT NULL, ADD ft_tot_stud_crhrs_tght VARCHAR(20) DEFAULT NULL, ADD ft_tot_cred_sec_tght VARCHAR(20) DEFAULT NULL, ADD pt_tot_fac VARCHAR(20) DEFAULT NULL, ADD pt_tot_stud_crhrs_tght VARCHAR(20) DEFAULT NULL, ADD pt_tot_cred_sec_tght VARCHAR(20) DEFAULT NULL, ADD tot_fac VARCHAR(20) DEFAULT NULL, ADD tot_cred_hrs VARCHAR(20) DEFAULT NULL, ADD ft_perc_crh VARCHAR(20) DEFAULT NULL, ADD pt_perc_crh VARCHAR(20) DEFAULT NULL, ADD tot_cred_sec VARCHAR(20) DEFAULT NULL, ADD ft_perc_sec VARCHAR(20) DEFAULT NULL, ADD pt_perc_sec VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP fy_dup_headc_bni, DROP comp_serv, DROP tot_inst_adm_cst, DROP tot_rev, DROP net_revenue_usd, DROP net_revenue_perc, DROP tot_cred_cou_sec, DROP tot_cred_stud, DROP av_cred_sec_size, DROP ft_tot_fac, DROP ft_tot_stud_crhrs_tght, DROP ft_tot_cred_sec_tght, DROP pt_tot_fac, DROP pt_tot_stud_crhrs_tght, DROP pt_tot_cred_sec_tght, DROP tot_fac, DROP tot_cred_hrs, DROP ft_perc_crh, DROP pt_perc_crh, DROP tot_cred_sec, DROP ft_perc_sec, DROP pt_perc_sec");
    }
}
