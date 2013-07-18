<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130506092704 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE subscriptions (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, observation_id INT DEFAULT NULL, year INT NOT NULL, status VARCHAR(255) NOT NULL, paymentMethod VARCHAR(255) DEFAULT NULL, paymentAmount DOUBLE PRECISION DEFAULT NULL, paymentDate DATE DEFAULT NULL, paymentName VARCHAR(255) DEFAULT NULL, paymentSystemName VARCHAR(255) DEFAULT NULL, paymentAddress VARCHAR(255) DEFAULT NULL, paymentAddress2 VARCHAR(255) DEFAULT NULL, paymentCity VARCHAR(255) DEFAULT NULL, paymentState VARCHAR(255) DEFAULT NULL, paymentZip VARCHAR(255) DEFAULT NULL, paymentEmail VARCHAR(255) DEFAULT NULL, paymentTransactionId VARCHAR(255) DEFAULT NULL, INDEX IDX_4778A01770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
        $this->addSql("ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A011409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id)");
        $this->addSql("ALTER TABLE users ADD prefix VARCHAR(255) DEFAULT NULL, ADD title VARCHAR(255) DEFAULT NULL, ADD phone VARCHAR(255) DEFAULT NULL, ADD extension VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE subscriptions");
        $this->addSql("ALTER TABLE users DROP prefix, DROP title, DROP phone, DROP extension");
    }
}
