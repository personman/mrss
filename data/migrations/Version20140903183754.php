<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140903183754 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ss_disabserv DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv DOUBLE PRECISION DEFAULT NULL, ADD ss_total DOUBLE PRECISION DEFAULT NULL, ADD ss_admissions_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_recruitment_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_advising_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_counseling_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_career_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_financial_aid_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_registrar_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_tutoring_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_testing_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_cocurricular_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_disabserv_budget DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_budget DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ss_disabserv, DROP ss_vetserv, DROP ss_total, DROP ss_admissions_budget, DROP ss_recruitment_budget, DROP ss_advising_budget, DROP ss_counseling_budget, DROP ss_career_budget, DROP ss_financial_aid_budget, DROP ss_registrar_budget, DROP ss_tutoring_budget, DROP ss_testing_budget, DROP ss_cocurricular_budget, DROP ss_disabserv_budget, DROP ss_vetserv_budget");
    }
}
