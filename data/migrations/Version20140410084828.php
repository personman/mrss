<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140410084828 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ss_admis_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_admiss_emp INT DEFAULT NULL, ADD ss_recruitment_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_recruitment_emp INT DEFAULT NULL, ADD ss_advising_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_advising_emp INT DEFAULT NULL, ADD ss_advising_student INT DEFAULT NULL, ADD ss_counseling_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_counseling_emp INT DEFAULT NULL, ADD ss_counseling_student INT DEFAULT NULL, ADD ss_career_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_career_emp INT DEFAULT NULL, ADD ss_career_student INT DEFAULT NULL, ADD ss_financial_aid_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_financial_aid_emp INT DEFAULT NULL, ADD ss_financial_aid_student INT DEFAULT NULL, ADD ss_registrar_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_registrar_emp INT DEFAULT NULL, ADD ss_tutoring_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_tutoring_emp INT DEFAULT NULL, ADD ss_tutoring_students INT DEFAULT NULL, ADD ss_testing_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_testing_emp INT DEFAULT NULL, ADD ss_testing_student INT DEFAULT NULL, ADD ss_cocurricular_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_cocurricular_emp INT DEFAULT NULL, ADD ss_disabserv_total DOUBLE PRECISION DEFAULT NULL, ADD ss_disabserv_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_disabserv_emp INT DEFAULT NULL, ADD ss_disabserv_o_students INT DEFAULT NULL, ADD ss_disabserv_contract DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_total DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_o_cost DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_emp INT DEFAULT NULL, ADD ss_vetserv_o_students INT DEFAULT NULL, ADD ss_vetserv_contract DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ss_admis_o_cost, DROP ss_admiss_emp, DROP ss_recruitment_o_cost, DROP ss_recruitment_emp, DROP ss_advising_o_cost, DROP ss_advising_emp, DROP ss_advising_student, DROP ss_counseling_o_cost, DROP ss_counseling_emp, DROP ss_counseling_student, DROP ss_career_o_cost, DROP ss_career_emp, DROP ss_career_student, DROP ss_financial_aid_o_cost, DROP ss_financial_aid_emp, DROP ss_financial_aid_student, DROP ss_registrar_o_cost, DROP ss_registrar_emp, DROP ss_tutoring_o_cost, DROP ss_tutoring_emp, DROP ss_tutoring_students, DROP ss_testing_o_cost, DROP ss_testing_emp, DROP ss_testing_student, DROP ss_cocurricular_o_cost, DROP ss_cocurricular_emp, DROP ss_disabserv_total, DROP ss_disabserv_o_cost, DROP ss_disabserv_emp, DROP ss_disabserv_o_students, DROP ss_disabserv_contract, DROP ss_vetserv_total, DROP ss_vetserv_o_cost, DROP ss_vetserv_emp, DROP ss_vetserv_o_students, DROP ss_vetserv_contract");
    }
}
