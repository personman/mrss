<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130723140900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE college_systems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, ipeds VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, city VARCHAR(10) DEFAULT NULL, state VARCHAR(2) DEFAULT NULL, zip VARCHAR(11) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE colleges ADD system_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE colleges ADD CONSTRAINT FK_F5AA74A0D0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id)");
        $this->addSql("CREATE INDEX IDX_F5AA74A0D0952FA5 ON colleges (system_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE colleges DROP FOREIGN KEY FK_F5AA74A0D0952FA5");
        $this->addSql("DROP TABLE college_systems");
        $this->addSql("DROP INDEX IDX_F5AA74A0D0952FA5 ON colleges");
        $this->addSql("ALTER TABLE colleges DROP system_id");
    }
}
