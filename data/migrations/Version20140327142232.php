<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140327142232 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE peer_groups (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, year INT NOT NULL, name VARCHAR(255) NOT NULL, states LONGTEXT NOT NULL, environments VARCHAR(255) NOT NULL, workforceEnrollment VARCHAR(255) NOT NULL, workforceRevenue VARCHAR(255) NOT NULL, serviceAreaPopulation VARCHAR(255) NOT NULL, serviceAreaUnemployment VARCHAR(255) NOT NULL, serviceAreaMedianIncome VARCHAR(255) NOT NULL, benchmarks LONGTEXT NOT NULL, peers LONGTEXT NOT NULL, INDEX IDX_CED87FA0770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE peer_groups ADD CONSTRAINT FK_CED87FA0770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
        $this->addSql("ALTER TABLE observations ADD enrollment_information_workforce_enrollment_percent DOUBLE PRECISION DEFAULT NULL, ADD enrollment_information_market_penetration DOUBLE PRECISION DEFAULT NULL, ADD enrollment_information_contact_hours_per_student VARCHAR(255) DEFAULT NULL, ADD retention_percent_returning_organizations_served DOUBLE PRECISION DEFAULT NULL, ADD retention_percent_returning_students DOUBLE PRECISION DEFAULT NULL, ADD staffing_full_time_instructors_percent DOUBLE PRECISION DEFAULT NULL, ADD staffing_part_time_instructors_percent INT DEFAULT NULL, ADD staffing_independent_contractors_percent DOUBLE PRECISION DEFAULT NULL, ADD staffing_instructor_staff_ratio VARCHAR(255) DEFAULT NULL, ADD revenue_contract_training_percent DOUBLE PRECISION DEFAULT NULL, ADD revenue_continuing_education_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_salaries_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_benefits_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_supplies_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_marketing_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_capital_equipment_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_travel_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_contract_training_percent DOUBLE PRECISION DEFAULT NULL, ADD expenditures_continuing_education_percent DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE peer_groups");
        $this->addSql("ALTER TABLE observations DROP enrollment_information_workforce_enrollment_percent, DROP enrollment_information_market_penetration, DROP enrollment_information_contact_hours_per_student, DROP retention_percent_returning_organizations_served, DROP retention_percent_returning_students, DROP staffing_full_time_instructors_percent, DROP staffing_part_time_instructors_percent, DROP staffing_independent_contractors_percent, DROP staffing_instructor_staff_ratio, DROP revenue_contract_training_percent, DROP revenue_continuing_education_percent, DROP expenditures_salaries_percent, DROP expenditures_benefits_percent, DROP expenditures_supplies_percent, DROP expenditures_marketing_percent, DROP expenditures_capital_equipment_percent, DROP expenditures_travel_percent, DROP expenditures_contract_training_percent, DROP expenditures_continuing_education_percent");
    }
}
