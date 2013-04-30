<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416154939 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE user_role");
        $this->addSql("DROP TABLE user_role_linker");
        $this->addSql("ALTER TABLE benchmark_groups ADD shortName VARCHAR(255) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE user_role (role_id VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, parent VARCHAR(255) DEFAULT NULL, PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE user_role_linker (user_id INT UNSIGNED NOT NULL, role_id VARCHAR(255) NOT NULL, INDEX role_id (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE benchmark_groups DROP shortName");
    }
}
