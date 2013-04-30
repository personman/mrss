<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130425090130 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE studies (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE benchmark_groups ADD study_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE benchmark_groups ADD CONSTRAINT FK_9B418C54E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("CREATE INDEX IDX_9B418C54E7B003E9 ON benchmark_groups (study_id)");
        $this->addSql("ALTER TABLE pages DROP FOREIGN KEY FK_2074E575E37ECFB0");
        $this->addSql("DROP INDEX IDX_2074E575E37ECFB0 ON pages");
        $this->addSql("ALTER TABLE pages DROP updater_id");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE benchmark_groups DROP FOREIGN KEY FK_9B418C54E7B003E9");
        $this->addSql("DROP TABLE studies");
        $this->addSql("DROP INDEX IDX_9B418C54E7B003E9 ON benchmark_groups");
        $this->addSql("ALTER TABLE benchmark_groups DROP study_id");
        $this->addSql("ALTER TABLE pages ADD updater_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE pages ADD CONSTRAINT FK_2074E575E37ECFB0 FOREIGN KEY (updater_id) REFERENCES users (id)");
        $this->addSql("CREATE INDEX IDX_2074E575E37ECFB0 ON pages (updater_id)");
    }
}
