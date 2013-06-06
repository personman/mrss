<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130605151938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // Create the NCCWTP study. Use "ON DUPLICATE KEY" for when it's already
        // there. "id=id" means it just does nothing.
        $this->addSql("INSERT INTO studies (id, name, description, currentYear) VALUES (3, 'NCCWTP', 'National Community College Workforce Training Project', 2013) ON DUPLICATE KEY UPDATE id=id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DELETE FROM studies WHERE id = 3");
    }
}
