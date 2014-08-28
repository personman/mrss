<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140819094737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_cred_hrs_per_full_faculty VARCHAR(255) DEFAULT NULL, ADD inst_full_expend_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_full_expend_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD inst_part_expend_per_fte DOUBLE PRECISION DEFAULT NULL, ADD inst_cred_hrs_per_part_faculty VARCHAR(255) DEFAULT NULL, ADD inst_part_expend_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_part_expend_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD inst_expend_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_expend_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD inst_expend_per_fte VARCHAR(255) DEFAULT NULL, ADD inst_exec_expend_per_fte DOUBLE PRECISION DEFAULT NULL, ADD inst_exec_expend_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_exec_expend_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD inst_total_expend_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_total_expend_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD inst_total_expend_per_employee DOUBLE PRECISION DEFAULT NULL, ADD tuition_fees_per_cred_hr DOUBLE PRECISION DEFAULT NULL, ADD inst_expend_o_rev DOUBLE PRECISION DEFAULT NULL, ADD inst_expend_covered_by_tuition DOUBLE PRECISION DEFAULT NULL, ADD inst_net_rev_per_cred_hr DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_cred_hrs_per_full_faculty, DROP inst_full_expend_per_cred_hr, DROP inst_full_expend_per_fte_student, DROP inst_part_expend_per_fte, DROP inst_cred_hrs_per_part_faculty, DROP inst_part_expend_per_cred_hr, DROP inst_part_expend_per_fte_student, DROP inst_expend_per_cred_hr, DROP inst_expend_per_fte_student, DROP inst_expend_per_fte, DROP inst_exec_expend_per_fte, DROP inst_exec_expend_per_cred_hr, DROP inst_exec_expend_per_fte_student, DROP inst_total_expend_per_cred_hr, DROP inst_total_expend_per_fte_student, DROP inst_total_expend_per_employee, DROP tuition_fees_per_cred_hr, DROP inst_expend_o_rev, DROP inst_expend_covered_by_tuition, DROP inst_net_rev_per_cred_hr");
    }
}
