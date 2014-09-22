<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140922101852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE changes DROP FOREIGN KEY FK_2020B83DE80D127B");
        $this->addSql("ALTER TABLE changes ADD CONSTRAINT FK_2020B83DE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE changes DROP FOREIGN KEY FK_2020B83DE80D127B");
        $this->addSql("ALTER TABLE changes ADD CONSTRAINT FK_2020B83DE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
    }
}
