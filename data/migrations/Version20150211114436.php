<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150211114436 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations ADD inst_cost_full_per_cred_hr_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_per_cred_hr_course_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_per_cred_hr_course_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_per_cred_hr_course_dev DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_full_per_cred_hr_program_dev, DROP inst_cost_full_per_cred_hr_course_dev, DROP inst_cost_part_per_cred_hr_course_dev, DROP inst_cost_total_per_cred_hr_course_dev');
    }
}
