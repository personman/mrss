<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140410112114 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD as_tech_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_tech_emp INT DEFAULT NULL, ADD as_tech_student INT DEFAULT NULL, ADD as_library_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_library_emp INT DEFAULT NULL, ADD as_experiential_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_emp INT DEFAULT NULL, ADD as_experiential_student INT DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP as_tech_o_cost, DROP as_tech_emp, DROP as_tech_student, DROP as_library_o_cost, DROP as_library_emp, DROP as_experiential_o_cost, DROP as_experiential_emp, DROP as_experiential_student");
    }
}
