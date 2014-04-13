<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140406191437 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_at_full_program_dev VARCHAR(255) DEFAULT NULL, ADD inst_at_full_course_dev VARCHAR(255) DEFAULT NULL, ADD inst_at_full_teaching VARCHAR(255) DEFAULT NULL, ADD inst_at_full_tutoring VARCHAR(255) DEFAULT NULL, ADD inst_at_full_advising VARCHAR(255) DEFAULT NULL, ADD inst_at_full_ac_service VARCHAR(255) DEFAULT NULL, ADD inst_at_full_assessment VARCHAR(255) DEFAULT NULL, ADD inst_at_full_pd VARCHAR(255) DEFAULT NULL, ADD inst_at_part_program_dev VARCHAR(255) DEFAULT NULL, ADD inst_at_part_course_dev VARCHAR(255) DEFAULT NULL, ADD inst_at_part_teaching VARCHAR(255) DEFAULT NULL, ADD inst_at_part_tutoring VARCHAR(255) DEFAULT NULL, ADD inst_at_part_advising VARCHAR(255) DEFAULT NULL, ADD inst_at_part_ac_service VARCHAR(255) DEFAULT NULL, ADD inst_at_part_assessment VARCHAR(255) DEFAULT NULL, ADD inst_at_part_pd VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_at_full_program_dev, DROP inst_at_full_course_dev, DROP inst_at_full_teaching, DROP inst_at_full_tutoring, DROP inst_at_full_advising, DROP inst_at_full_ac_service, DROP inst_at_full_assessment, DROP inst_at_full_pd, DROP inst_at_part_program_dev, DROP inst_at_part_course_dev, DROP inst_at_part_teaching, DROP inst_at_part_tutoring, DROP inst_at_part_advising, DROP inst_at_part_ac_service, DROP inst_at_part_assessment, DROP inst_at_part_pd");
    }
}
