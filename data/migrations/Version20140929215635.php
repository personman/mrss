<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140929215635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AB99022A0");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6A74F45A78 FOREIGN KEY (subobservation_id) REFERENCES sub_observations (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6A74F45A78");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AB99022A0 FOREIGN KEY (subObservation_id) REFERENCES sub_observations (id)");
    }
}
