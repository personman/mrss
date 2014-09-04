<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140904001830 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ss_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_contract DOUBLE PRECISION DEFAULT NULL, ADD ss_admissions_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_recruitment_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_advising_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_counseling_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_career_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_financial_aid_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_registrar_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_tutorings_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_testing_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_cocurricular_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_disabserv_percent_salaries DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_percent_salaries DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ss_salaries, DROP ss_o_cost, DROP ss_contract, DROP ss_admissions_percent_salaries, DROP ss_recruitment_percent_salaries, DROP ss_advising_percent_salaries, DROP ss_counseling_percent_salaries, DROP ss_career_percent_salaries, DROP ss_financial_aid_percent_salaries, DROP ss_registrar_percent_salaries, DROP ss_tutorings_percent_salaries, DROP ss_testing_percent_salaries, DROP ss_cocurricular_percent_salaries, DROP ss_disabserv_percent_salaries, DROP ss_vetserv_percent_salaries");
    }
}
