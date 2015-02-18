<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150217150810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD as_tech_cost_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_cost_per_fte_student DOUBLE PRECISION DEFAULT NULL, ADD as_students_per_tech_emp DOUBLE PRECISION DEFAULT NULL, ADD as_students_per_library_emp DOUBLE PRECISION DEFAULT NULL, ADD as_students_per_experiential_emp DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP as_tech_cost_per_fte_student, DROP as_experiential_cost_per_fte_student, DROP as_students_per_tech_emp, DROP as_students_per_library_emp, DROP as_students_per_experiential_emp');
    }
}
