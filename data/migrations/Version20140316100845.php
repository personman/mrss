<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140316100845 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE percentiles (id INT AUTO_INCREMENT NOT NULL, benchmark_id INT DEFAULT NULL, year INT NOT NULL, cipCode DOUBLE PRECISION DEFAULT NULL, percentile INT NOT NULL, value DOUBLE PRECISION NOT NULL, INDEX IDX_131D1EEEE80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE percentile_ranks (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, benchmark_id INT DEFAULT NULL, year INT NOT NULL, cipCode DOUBLE PRECISION DEFAULT NULL, rank DOUBLE PRECISION NOT NULL, INDEX IDX_E9BB4DFA770124B2 (college_id), INDEX IDX_E9BB4DFAE80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE percentiles ADD CONSTRAINT FK_131D1EEEE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFA770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFAE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE percentiles");
        $this->addSql("DROP TABLE percentile_ranks");
    }
}
