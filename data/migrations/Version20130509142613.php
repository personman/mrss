<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130509142613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // Create the NCCBP study. Use "ON DUPLICATE KEY" for when it's already
        // there. "id=id" means it just does nothing.
        $this->addSql("INSERT INTO studies (id, name, description) VALUES (1, 'NCCBP', 'National Community College Benchmark Project') ON DUPLICATE KEY UPDATE id=id");

        // Now add MRSS
        $this->addSql("INSERT INTO studies (id, name, description) VALUES (2, 'MRSS', 'Maximizing Resources for Student Success') ON DUPLICATE KEY UPDATE id=id");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DELETE FROM studies WHERE id = 1");
        $this->addSql("DELETE FROM studies WHERE id = 2");

    }
}
