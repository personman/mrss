<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150408094214 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD inst_cost_perc_taught_by_ft DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_perc_taught_by_pt DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sub_observations ADD inst_cost_perc_taught_by_ft DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_perc_taught_by_pt DOUBLE PRECISION DEFAULT NULL');

        $this->addSql("INSERT INTO benchmarks (benchmarkGroup_id, dbColumn, name, inputType, yearsAvailable, computed, equation, sequence, reportSequence) VALUES (41, 'inst_cost_perc_taught_by_ft', 'Percent of FTE Students Taught by Full-time', 'percent', '2014,2015,2016,2017', TRUE, '{{inst_cost_full_cred_hr}} / ({{inst_cost_full_cred_hr}} + {{inst_cost_part_cred_hr}})', (SELECT MAX(sequence) + 1 FROM benchmarks b2 WHERE benchmarkGroup_id = 41), (SELECT MAX(reportSequence) + 1 FROM benchmarks b3 WHERE benchmarkGroup_id = 41))");

        $this->addSql("INSERT INTO benchmarks (benchmarkGroup_id, dbColumn, name, inputType, yearsAvailable, computed, equation, sequence, reportSequence) VALUES (41, 'inst_cost_perc_taught_by_pt', 'Percent of FTE Students Taught by Part-time', 'percent', '2014,2015,2016,2017', TRUE, '{{inst_cost_part_cred_hr}} / ({{inst_cost_full_cred_hr}} + {{inst_cost_part_cred_hr}})', (SELECT MAX(sequence) + 1 FROM benchmarks b2 WHERE benchmarkGroup_id = 41), (SELECT MAX(reportSequence) + 1 FROM benchmarks b3 WHERE benchmarkGroup_id = 41))");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP inst_cost_perc_taught_by_ft, DROP inst_cost_perc_taught_by_pt');
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_perc_taught_by_ft, DROP inst_cost_perc_taught_by_pt');
    }
}
