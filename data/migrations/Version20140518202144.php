<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140518202144 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE outliers (id INT AUTO_INCREMENT NOT NULL, benchmark_id INT DEFAULT NULL, observation_id INT DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, problem VARCHAR(255) NOT NULL, INDEX IDX_C2FFCDD9E80D127B (benchmark_id), INDEX IDX_C2FFCDD91409DD88 (observation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD91409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE outliers");
    }
}
