<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130401224227 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE observations (
        id INT AUTO_INCREMENT NOT NULL,
        college_id INT DEFAULT NULL,
        year INT NOT NULL,
        cipCode DOUBLE PRECISION NOT NULL,
        INDEX IDX_BBC15BA8770124B2 (college_id),
        PRIMARY KEY(id)
        )
        DEFAULT CHARACTER SET utf8
        COLLATE utf8_unicode_ci ENGINE = InnoDB");

        $this->addSql("ALTER TABLE observations ADD CONSTRAINT FK_BBC15BA8770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE observations");
    }
}
