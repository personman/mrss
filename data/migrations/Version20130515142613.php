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

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");


        $this->addSql("ALTER TABLE studies DROP price, DROP earlyPrice, DROP earlyPriceDate, DROP enrollmentOpen, DROP dataEntryOpen, DROP reportsOpen, DROP uPayUrl, DROP uPaySiteId");

    }
}
