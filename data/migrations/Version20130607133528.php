<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130607133528 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD enrollment_information_duplicated_enrollment INT DEFAULT NULL, ADD enrollment_information_unduplicated_enrollment INT DEFAULT NULL, ADD enrollment_information_organizations_served INT DEFAULT NULL, ADD enrollment_information_training_contracts INT DEFAULT NULL, ADD enrollment_information_total_contact_hours INT DEFAULT NULL, ADD retention_returning_organizations INT DEFAULT NULL, ADD retention_returning_students INT DEFAULT NULL, ADD staffing_full_time_instructors INT DEFAULT NULL, ADD staffing_part_time_instructors INT DEFAULT NULL, ADD staffing_independent_contractors INT DEFAULT NULL, ADD staffing_full_time_support_staff INT DEFAULT NULL, ADD staffing_part_time_support_staff INT DEFAULT NULL, ADD transition_students INT DEFAULT NULL, ADD revenue_federal DOUBLE PRECISION DEFAULT NULL, ADD revenue_state DOUBLE PRECISION DEFAULT NULL, ADD revenue_local DOUBLE PRECISION DEFAULT NULL, ADD revenue_grants DOUBLE PRECISION DEFAULT NULL, ADD revenue_earned_revenue DOUBLE PRECISION DEFAULT NULL, ADD revenue_contract_training DOUBLE PRECISION DEFAULT NULL, ADD revenue_continuing_education DOUBLE PRECISION DEFAULT NULL, ADD revenue_total DOUBLE PRECISION DEFAULT NULL, ADD expenditures_salaries DOUBLE PRECISION DEFAULT NULL, ADD expenditures_benefits DOUBLE PRECISION DEFAULT NULL, ADD expenditures_supplies DOUBLE PRECISION DEFAULT NULL, ADD expenditures_marketing DOUBLE PRECISION DEFAULT NULL, ADD expenditures_capital_equipment DOUBLE PRECISION DEFAULT NULL, ADD expenditures_travel DOUBLE PRECISION DEFAULT NULL, ADD expenditures_contract_training DOUBLE PRECISION DEFAULT NULL, ADD expenditures_continuing_education DOUBLE PRECISION DEFAULT NULL, ADD expenditures_total DOUBLE PRECISION DEFAULT NULL, ADD expenditures_overhead DOUBLE PRECISION DEFAULT NULL, ADD retained_revenue_contract_training VARCHAR(255) DEFAULT NULL, ADD retained_revenue_continuing_education DOUBLE PRECISION DEFAULT NULL, ADD retained_revenue_total DOUBLE PRECISION DEFAULT NULL, ADD retained_revenue_roi DOUBLE PRECISION DEFAULT NULL, ADD satisfaction_client DOUBLE PRECISION DEFAULT NULL, ADD satisfaction_student DOUBLE PRECISION DEFAULT NULL, ADD institutional_demographics_credit_enrollment INT DEFAULT NULL, ADD institutional_demographics_operating_revenue DOUBLE PRECISION DEFAULT NULL, ADD institutional_demographics_campus_environment VARCHAR(255) DEFAULT NULL, ADD institutional_demographics_faculty_unionized VARCHAR(255) DEFAULT NULL, ADD institutional_demographics_staff_unionized VARCHAR(255) DEFAULT NULL, ADD institutional_demographics_total_population INT DEFAULT NULL, ADD institutional_demographics_total_companies INT DEFAULT NULL, ADD institutional_demographics_companies_less_than_50 INT DEFAULT NULL, ADD institutional_demographics_companies_50_to_99 INT DEFAULT NULL, ADD institutional_demographics_companies_100_to_499 INT DEFAULT NULL, ADD institutional_demographics_companies_500_or_greater INT DEFAULT NULL, ADD institutional_demographics_unemployment_rate DOUBLE PRECISION DEFAULT NULL, ADD institutional_demographics_median_household_income DOUBLE PRECISION DEFAULT NULL, ADD institutional_demographics_credentials_awarded INT DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP enrollment_information_duplicated_enrollment, DROP enrollment_information_unduplicated_enrollment, DROP enrollment_information_organizations_served, DROP enrollment_information_training_contracts, DROP enrollment_information_total_contact_hours, DROP retention_returning_organizations, DROP retention_returning_students, DROP staffing_full_time_instructors, DROP staffing_part_time_instructors, DROP staffing_independent_contractors, DROP staffing_full_time_support_staff, DROP staffing_part_time_support_staff, DROP transition_students, DROP revenue_federal, DROP revenue_state, DROP revenue_local, DROP revenue_grants, DROP revenue_earned_revenue, DROP revenue_contract_training, DROP revenue_continuing_education, DROP revenue_total, DROP expenditures_salaries, DROP expenditures_benefits, DROP expenditures_supplies, DROP expenditures_marketing, DROP expenditures_capital_equipment, DROP expenditures_travel, DROP expenditures_contract_training, DROP expenditures_continuing_education, DROP expenditures_total, DROP expenditures_overhead, DROP retained_revenue_contract_training, DROP retained_revenue_continuing_education, DROP retained_revenue_total, DROP retained_revenue_roi, DROP satisfaction_client, DROP satisfaction_student, DROP institutional_demographics_credit_enrollment, DROP institutional_demographics_operating_revenue, DROP institutional_demographics_campus_environment, DROP institutional_demographics_faculty_unionized, DROP institutional_demographics_staff_unionized, DROP institutional_demographics_total_population, DROP institutional_demographics_total_companies, DROP institutional_demographics_companies_less_than_50, DROP institutional_demographics_companies_50_to_99, DROP institutional_demographics_companies_100_to_499, DROP institutional_demographics_companies_500_or_greater, DROP institutional_demographics_unemployment_rate, DROP institutional_demographics_median_household_income, DROP institutional_demographics_credentials_awarded");
    }
}