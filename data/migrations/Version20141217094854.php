<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141217094854 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE benchmarks ADD reportSequence INT NOT NULL');
        $this->addSql('ALTER TABLE benchmark_headings ADD type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD adminBenchmarkSorting VARCHAR(255) DEFAULT NULL');

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE benchmark_headings DROP type');
        $this->addSql('ALTER TABLE benchmarks DROP reportSequence');
        $this->addSql('DROP INDEX idx_f75a0c6a74f45a78 ON change_sets');
        $this->addSql('CREATE INDEX IDX_F75A0C6AB99022A0 ON change_sets (subObservation_id)');
        $this->addSql('DROP INDEX idx_4778a011409dd88 ON subscriptions');
        $this->addSql('CREATE INDEX FK_4778A011409DD88 ON subscriptions (observation_id)');
        $this->addSql('ALTER TABLE users DROP adminBenchmarkSorting');
    }
}
