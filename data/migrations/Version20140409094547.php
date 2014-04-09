<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140409094547 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // For deployment of mrss data collection
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 1: Institutional Instructional Costs', shortName = 'Instruction' where id = 37");
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 2: Instructional Costs and Activities', shortName = 'Instructional Costs' WHERE id = 41");
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 3: Student Services Activities', shortName = 'Student Services' WHERE id = 38");
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 4: Academic Support Activities', shortName = 'Academic Support' WHERE id = 39");
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 5: Demographics', shortName = 'Demographics' WHERE id = 40");
        $this->addSql("UPDATE benchmark_groups SET name = 'Form 6: Student Success Metrics', shortName = 'Student Success' WHERE id = 42");

        // Now delete some benchmarks (the importer doesn't trigger deletes
        $this->addSql("delete from percentile_ranks");
        $this->addSql("delete from percentiles");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn LIKE '%\_ct' OR dbColumn LIKE '%\_at'");
        $this->addSql("delete from benchmarks where benchmarkGroup_id = 41");

        $this->addSql("DELETE FROM benchmarks WHERE dbColumn LIKE '%other_specify'");
        $this->addSql("delete from benchmarks where dbColumn like 'inst_total%';");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
    }
}
