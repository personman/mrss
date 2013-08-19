<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130819095034 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE payment_queue (id INT AUTO_INCREMENT NOT NULL, transId VARCHAR(255) NOT NULL, postback LONGTEXT NOT NULL, processed TINYINT(1) DEFAULT NULL, created DATETIME NOT NULL, processedDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE subscriptions DROP INDEX FK_4778A011409DD88, ADD UNIQUE INDEX UNIQ_4778A011409DD88 (observation_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE payment_queue");
        $this->addSql("ALTER TABLE subscriptions DROP INDEX UNIQ_4778A011409DD88, ADD INDEX FK_4778A011409DD88 (observation_id)");
    }
}
