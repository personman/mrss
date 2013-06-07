<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130515142613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");


        $this->addSql("ALTER TABLE studies ADD price DOUBLE PRECISION NOT NULL, ADD earlyPrice DOUBLE PRECISION NOT NULL, ADD earlyPriceDate DATE NOT NULL, ADD enrollmentOpen TINYINT(1) NOT NULL, ADD dataEntryOpen TINYINT(1) NOT NULL, ADD reportsOpen TINYINT(1) NOT NULL, ADD uPayUrl VARCHAR(255) NOT NULL, ADD uPaySiteId INT NOT NULL");

        // Create the NCCBP study. Use "ON DUPLICATE KEY" for when it's already
        // there. "id=id" means it just does nothing.
        $this->addSql("INSERT INTO studies (id, name, description, currentYear, uPaySiteId, uPayUrl) VALUES (1, 'NCCBP', 'National Community College Benchmark Project', 2013, 3, 'http://test.com') ON DUPLICATE KEY UPDATE id=id");

        // Now add MRSS
        $this->addSql("INSERT INTO studies (id, name, description, currentYear, uPaySiteId, uPayUrl) VALUES (2, 'MRSS', 'Maximizing Resources for Student Success', 2013, 3, 'http://test.com') ON DUPLICATE KEY UPDATE id=id");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DELETE FROM studies WHERE id = 1");
        $this->addSql("DELETE FROM studies WHERE id = 2");

        $this->addSql("ALTER TABLE studies DROP price, DROP earlyPrice, DROP earlyPriceDate, DROP enrollmentOpen, DROP dataEntryOpen, DROP reportsOpen, DROP uPayUrl, DROP uPaySiteId");

    }
}
