<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140911112739 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentiles DROP FOREIGN KEY FK_131D1EEEE80D127B");
        $this->addSql("ALTER TABLE percentiles ADD CONSTRAINT FK_131D1EEEE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFAE80D127B");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFAE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFAE80D127B");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFAE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
        $this->addSql("ALTER TABLE percentiles DROP FOREIGN KEY FK_131D1EEEE80D127B");
        $this->addSql("ALTER TABLE percentiles ADD CONSTRAINT FK_131D1EEEE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
    }
}
