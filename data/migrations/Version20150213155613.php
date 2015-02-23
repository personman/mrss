<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150213155613 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD inst_cost_full_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sub_observations ADD inst_cost_full_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_total_expend_per_fte_faculty DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP inst_cost_full_expend_per_fte_faculty, DROP inst_cost_part_expend_per_fte_faculty, DROP inst_cost_total_expend_per_fte_faculty');
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_full_expend_per_fte_faculty, DROP inst_cost_part_expend_per_fte_faculty, DROP inst_cost_total_expend_per_fte_faculty');
    }
}
