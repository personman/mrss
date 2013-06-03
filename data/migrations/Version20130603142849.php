<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130603142849 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD undup_cre_head VARCHAR(20) DEFAULT NULL, ADD undup_non_cre_head VARCHAR(20) DEFAULT NULL, ADD serv_pop VARCHAR(20) DEFAULT NULL, ADD cre_stud_pen_rate VARCHAR(20) DEFAULT NULL, ADD ncre_stud_pen_rate VARCHAR(20) DEFAULT NULL, ADD cul_act_dupl_head VARCHAR(20) DEFAULT NULL, ADD pub_meet_dupl_head VARCHAR(20) DEFAULT NULL, ADD spo_dupl_head VARCHAR(20) DEFAULT NULL, ADD group_form14b_serv_pop VARCHAR(20) DEFAULT NULL, ADD cul_com_part VARCHAR(20) DEFAULT NULL, ADD pub_com_part VARCHAR(20) DEFAULT NULL, ADD spo_com_part VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP undup_cre_head, DROP undup_non_cre_head, DROP serv_pop, DROP cre_stud_pen_rate, DROP ncre_stud_pen_rate, DROP cul_act_dupl_head, DROP pub_meet_dupl_head, DROP spo_dupl_head, DROP group_form14b_serv_pop, DROP cul_com_part, DROP pub_com_part, DROP spo_com_part");
    }
}
