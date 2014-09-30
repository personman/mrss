<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140929215503 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6A1409DD88");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6A1409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6A1409DD88");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6A1409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id)");
    }
}
