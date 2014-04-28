<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140427130831 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets ADD subObservation_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AB99022A0 FOREIGN KEY (subObservation_id) REFERENCES sub_observations (id)");
        $this->addSql("CREATE INDEX IDX_F75A0C6AB99022A0 ON change_sets (subObservation_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AB99022A0");
        $this->addSql("DROP INDEX IDX_F75A0C6AB99022A0 ON change_sets");
        $this->addSql("ALTER TABLE change_sets DROP subObservation_id");
    }
}
