<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130603151117 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD tot_dis_lear_stud_hrs VARCHAR(20) DEFAULT NULL, ADD tot_dis_lear_sec VARCHAR(20) DEFAULT NULL, ADD tot_crh_tght VARCHAR(20) DEFAULT NULL, ADD tot_crs_tght VARCHAR(20) DEFAULT NULL, ADD dist_prop_crh VARCHAR(20) DEFAULT NULL, ADD dist_prop_crs VARCHAR(20) DEFAULT NULL, ADD group_form17b_a VARCHAR(20) DEFAULT NULL, ADD group_form17b_b VARCHAR(20) DEFAULT NULL, ADD group_form17b_c VARCHAR(20) DEFAULT NULL, ADD group_form17b_d VARCHAR(20) DEFAULT NULL, ADD group_form17b_p VARCHAR(20) DEFAULT NULL, ADD group_form17b_f VARCHAR(20) DEFAULT NULL, ADD group_form17b_w VARCHAR(20) DEFAULT NULL, ADD group_form17b_total VARCHAR(20) DEFAULT NULL, ADD group_form17b_a_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_b_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_c_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_d_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_p_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_f_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_w_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_withdrawal VARCHAR(20) DEFAULT NULL, ADD group_form17b_completed VARCHAR(20) DEFAULT NULL, ADD completer_succ VARCHAR(20) DEFAULT NULL, ADD group_form17b_enr_succ VARCHAR(20) DEFAULT NULL, ADD group_form17b_anb VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP tot_dis_lear_stud_hrs, DROP tot_dis_lear_sec, DROP tot_crh_tght, DROP tot_crs_tght, DROP dist_prop_crh, DROP dist_prop_crs, DROP group_form17b_a, DROP group_form17b_b, DROP group_form17b_c, DROP group_form17b_d, DROP group_form17b_p, DROP group_form17b_f, DROP group_form17b_w, DROP group_form17b_total, DROP group_form17b_a_perc, DROP group_form17b_b_perc, DROP group_form17b_c_perc, DROP group_form17b_d_perc, DROP group_form17b_p_perc, DROP group_form17b_f_perc, DROP group_form17b_w_perc, DROP group_form17b_withdrawal, DROP group_form17b_completed, DROP completer_succ, DROP group_form17b_enr_succ, DROP group_form17b_anb");
    }
}