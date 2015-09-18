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
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP inst_cost_perc_taught_by_ft, DROP inst_cost_perc_taught_by_pt');
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_perc_taught_by_ft, DROP inst_cost_perc_taught_by_pt');
    }
}
