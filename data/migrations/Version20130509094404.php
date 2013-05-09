<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130509094404 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE subscriptions ADD study_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("CREATE INDEX IDX_4778A01E7B003E9 ON subscriptions (study_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01E7B003E9");
        $this->addSql("DROP INDEX IDX_4778A01E7B003E9 ON subscriptions");
        $this->addSql("ALTER TABLE subscriptions DROP study_id");
    }
}
