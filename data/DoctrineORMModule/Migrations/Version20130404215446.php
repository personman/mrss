<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130404215446 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD tot_undup_cr_hd VARCHAR(20) DEFAULT NULL, ADD tot_fte_career_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_counc_adv_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_recr_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_fin_aid_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_stud_act_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_test_ass_staff VARCHAR(20) DEFAULT NULL, ADD career_staff_ratio VARCHAR(20) DEFAULT NULL, ADD couns_adv_ratio VARCHAR(20) DEFAULT NULL, ADD recr_staff_ratio VARCHAR(20) DEFAULT NULL, ADD fin_aid_staff_ratio VARCHAR(20) DEFAULT NULL, ADD stud_act_staff_ratio VARCHAR(20) DEFAULT NULL, ADD test_ass_staff_ratio VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP tot_undup_cr_hd, DROP tot_fte_career_staff, DROP tot_fte_counc_adv_staff, DROP tot_fte_recr_staff, DROP tot_fte_fin_aid_staff, DROP tot_fte_stud_act_staff, DROP tot_fte_test_ass_staff, DROP career_staff_ratio, DROP couns_adv_ratio, DROP recr_staff_ratio, DROP fin_aid_staff_ratio, DROP stud_act_staff_ratio, DROP test_ass_staff_ratio");
    }
}
