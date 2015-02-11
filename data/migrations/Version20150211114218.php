<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150211114218 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations ADD inst_cost_total_per_cred_hr_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_course_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_course_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_prof_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_prof_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_prof_dev DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_total_per_cred_hr_program_dev, DROP inst_cost_part_per_cred_hr_program_dev, DROP inst_cost_full_expend_course_dev, DROP inst_cost_part_expend_course_dev, DROP inst_cost_full_per_cred_hr_teaching, DROP inst_cost_part_per_cred_hr_teaching, DROP inst_cost_total_per_cred_hr_teaching, DROP inst_cost_full_per_cred_hr_tutoring, DROP inst_cost_part_per_cred_hr_tutoring, DROP inst_cost_total_per_cred_hr_tutoring, DROP inst_cost_full_per_cred_hr_advising, DROP inst_cost_part_per_cred_hr_advising, DROP inst_cost_total_per_cred_hr_advising, DROP inst_cost_full_per_cred_hr_ac_service, DROP inst_cost_part_per_cred_hr_ac_service, DROP inst_cost_total_per_cred_hr_ac_service, DROP inst_cost_full_per_cred_hr_prof_dev, DROP inst_cost_part_per_cred_hr_prof_dev, DROP inst_cost_total_per_cred_hr_prof_dev');
    }
}
