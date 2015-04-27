<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150423154752 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD ss_salaries_perc_of_admissions DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_admissions DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_admissions DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_recruitment DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_recruitment DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_recruitment DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_advising DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_advising DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_advising DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_counseling DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_counseling DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_counseling DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_career DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_career DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_career DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_financial_aid DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_financial_aid DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_financial_aid DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_registrar DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_registrar DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_registrar DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_tutoring DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_tutoring DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_tutoring DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_testing DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_testing DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_testing DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_cocurricular DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_cocurricular DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_cocurricular DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_disabserv DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_disabserv DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_disabserv DOUBLE PRECISION DEFAULT NULL, ADD ss_salaries_perc_of_vetserv DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost_perc_of_vetserv DOUBLE PRECISION DEFAULT NULL, ADD ss_contract_perc_of_vetserv DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP ss_salaries_perc_of_admissions, DROP ss_o_cost_perc_of_admissions, DROP ss_contract_perc_of_admissions, DROP ss_salaries_perc_of_recruitment, DROP ss_o_cost_perc_of_recruitment, DROP ss_contract_perc_of_recruitment, DROP ss_salaries_perc_of_advising, DROP ss_o_cost_perc_of_advising, DROP ss_contract_perc_of_advising, DROP ss_salaries_perc_of_counseling, DROP ss_o_cost_perc_of_counseling, DROP ss_contract_perc_of_counseling, DROP ss_salaries_perc_of_career, DROP ss_o_cost_perc_of_career, DROP ss_contract_perc_of_career, DROP ss_salaries_perc_of_financial_aid, DROP ss_o_cost_perc_of_financial_aid, DROP ss_contract_perc_of_financial_aid, DROP ss_salaries_perc_of_registrar, DROP ss_o_cost_perc_of_registrar, DROP ss_contract_perc_of_registrar, DROP ss_salaries_perc_of_tutoring, DROP ss_o_cost_perc_of_tutoring, DROP ss_contract_perc_of_tutoring, DROP ss_salaries_perc_of_testing, DROP ss_o_cost_perc_of_testing, DROP ss_contract_perc_of_testing, DROP ss_salaries_perc_of_cocurricular, DROP ss_o_cost_perc_of_cocurricular, DROP ss_contract_perc_of_cocurricular, DROP ss_salaries_perc_of_disabserv, DROP ss_o_cost_perc_of_disabserv, DROP ss_contract_perc_of_disabserv, DROP ss_salaries_perc_of_vetserv, DROP ss_o_cost_perc_of_vetserv, DROP ss_contract_perc_of_vetserv');
    }
}
