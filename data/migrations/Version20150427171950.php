<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150427171950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD as_salaries_perc_of_tech DOUBLE PRECISION DEFAULT NULL, ADD as_o_cost_perc_of_tech DOUBLE PRECISION DEFAULT NULL, ADD as_contract_perc_of_tech DOUBLE PRECISION DEFAULT NULL, ADD as_salaries_perc_of_library DOUBLE PRECISION DEFAULT NULL, ADD as_o_cost_perc_of_library DOUBLE PRECISION DEFAULT NULL, ADD as_contract_perc_of_library DOUBLE PRECISION DEFAULT NULL, ADD as_salaries_perc_of_experiential DOUBLE PRECISION DEFAULT NULL, ADD as_o_cost_perc_of_experiential DOUBLE PRECISION DEFAULT NULL, ADD as_contract_perc_of_experiential DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP as_salaries_perc_of_tech, DROP as_o_cost_perc_of_tech, DROP as_contract_perc_of_tech, DROP as_salaries_perc_of_library, DROP as_o_cost_perc_of_library, DROP as_contract_perc_of_library, DROP as_salaries_perc_of_experiential, DROP as_o_cost_perc_of_experiential, DROP as_contract_perc_of_experiential');
    }
}
