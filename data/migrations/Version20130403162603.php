<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130403162603 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE users ADD college_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE users ADD CONSTRAINT FK_1483A5E9770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
        $this->addSql("CREATE INDEX IDX_1483A5E9770124B2 ON users (college_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9770124B2");
        $this->addSql("DROP INDEX IDX_1483A5E9770124B2 ON users");
        $this->addSql("ALTER TABLE users DROP college_id");
    }
}
