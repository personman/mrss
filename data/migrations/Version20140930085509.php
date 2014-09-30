<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140930085509 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9770124B2");
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9E80D127B");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9E80D127B");
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9770124B2");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
    }
}
