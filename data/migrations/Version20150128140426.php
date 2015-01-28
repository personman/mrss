<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150128140426 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD revenue_other DOUBLE PRECISION DEFAULT NULL, ADD revenue_other_specify LONGTEXT DEFAULT NULL, ADD expenditures_for_other DOUBLE PRECISION DEFAULT NULL, ADD expenditures_other DOUBLE PRECISION DEFAULT NULL, ADD expenditures_other_specify LONGTEXT DEFAULT NULL, ADD expenditures_overhead_costs DOUBLE PRECISION DEFAULT NULL, ADD net_revenue_contract_training VARCHAR(255) DEFAULT NULL, ADD net_revenue_other VARCHAR(255) DEFAULT NULL, ADD net_revenue_other_specify LONGTEXT DEFAULT NULL, ADD net_revenue_continuing_education DOUBLE PRECISION DEFAULT NULL, ADD net_revenue_total DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP revenue_other, DROP revenue_other_specify, DROP expenditures_for_other, DROP expenditures_other, DROP expenditures_other_specify, DROP expenditures_overhead_costs, DROP net_revenue_contract_training, DROP net_revenue_other, DROP net_revenue_other_specify, DROP net_revenue_continuing_education, DROP net_revenue_total');
    }
}
