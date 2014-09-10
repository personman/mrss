<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140910143549 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_fte_students VARCHAR(255) DEFAULT NULL, ADD as_tech_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD as_library_cost_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD as_fte_students_per_tech_fte_emp VARCHAR(255) DEFAULT NULL, ADD as_fte_students_per_library_fte_emp VARCHAR(255) DEFAULT NULL, ADD as_fte_students_per_experiential_fte_emp VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_fte_students, DROP as_tech_cost_per_contact, DROP as_experiential_cost_per_contact, DROP as_library_cost_per_fte_student, DROP as_fte_students_per_tech_fte_emp, DROP as_fte_students_per_library_fte_emp, DROP as_fte_students_per_experiential_fte_emp");
    }
}
