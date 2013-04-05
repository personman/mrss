<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130405231026 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE benchmarks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, dbColumn VARCHAR(255) NOT NULL, sequence INT NOT NULL, benchmarkGroup_id INT DEFAULT NULL, INDEX IDX_41BC1C584F029208 (benchmarkGroup_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE benchmark_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sequence INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE benchmarks ADD CONSTRAINT FK_41BC1C584F029208 FOREIGN KEY (benchmarkGroup_id) REFERENCES benchmark_groups (id)");
        $this->addSql("DROP TABLE phinxlog");
        $this->addSql("DROP TABLE user");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE benchmarks DROP FOREIGN KEY FK_41BC1C584F029208");
        $this->addSql("CREATE TABLE phinxlog (version BIGINT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE user (user_id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, display_name VARCHAR(50) DEFAULT NULL, password VARCHAR(128) NOT NULL, state INT DEFAULT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("DROP TABLE benchmarks");
        $this->addSql("DROP TABLE benchmark_groups");
    }
}
