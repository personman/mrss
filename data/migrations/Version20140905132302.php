<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140905132302 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD as_total DOUBLE PRECISION DEFAULT NULL, ADD as_salaries DOUBLE PRECISION DEFAULT NULL, ADD as_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_contract DOUBLE PRECISION DEFAULT NULL, ADD as_tech_budget DOUBLE PRECISION DEFAULT NULL, ADD as_library_budget DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_budget DOUBLE PRECISION DEFAULT NULL, ADD as_tech_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD as_library_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD as_tech_percent_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_library_percent_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_percent_o_cost DOUBLE PRECISION DEFAULT NULL, ADD as_tech_cost_per_fte_emp DOUBLE PRECISION DEFAULT NULL, ADD as_library_cost_per_fte_emp DOUBLE PRECISION DEFAULT NULL, ADD as_experiential_cost_per_fte_emp DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP as_total, DROP as_salaries, DROP as_o_cost, DROP as_contract, DROP as_tech_budget, DROP as_library_budget, DROP as_experiential_budget, DROP as_tech_percent_salaries, DROP as_library_percent_salaries, DROP as_experiential_percent_salaries, DROP as_tech_percent_o_cost, DROP as_library_percent_o_cost, DROP as_experiential_percent_o_cost, DROP as_tech_cost_per_fte_emp, DROP as_library_cost_per_fte_emp, DROP as_experiential_cost_per_fte_emp");
    }
}
