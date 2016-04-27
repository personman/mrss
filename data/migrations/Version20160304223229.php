<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160304223229 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*$this->addSql('ALTER TABLE observations ADD ft_salaries_9_month DOUBLE PRECISION DEFAULT NULL, ADD ft_salaries_12_month DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_professor_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_associate_professor_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_assistant_professor_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_instructor_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_lecturer_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_no_rank_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_all_ranks_fica DOUBLE PRECISION DEFAULT NULL, ADD ft_average_retirement_per_all_ranks INT DEFAULT NULL, ADD ft_average_medical_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_dental_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_combined_medical_dental_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_disability_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_tuition_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_fica_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_unemployment_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_group_life_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_worker_comp_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_averge_other_per_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_all_combined_per_all_ranks DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_male_faculty_salary_9_month ft_average_male_faculty_salary_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_female_faculty_salary_9_month ft_average_female_faculty_salary_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_combined_faculty_salary_9_month ft_average_combined_faculty_salary_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_male_faculty_salary_12_month ft_average_male_faculty_salary_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_female_faculty_salary_12_month ft_average_female_faculty_salary_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_average_combined_faculty_salary_12_month ft_average_combined_faculty_salary_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_associate_professor_9_month ft_total_benefits_covered_associate_professor_9_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_instructor_9_month ft_total_benefits_covered_instructor_9_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_no_rank_9_month ft_total_benefits_covered_no_rank_9_month INT DEFAULT NULL, CHANGE ft_total_benefits_total_covered_9_month ft_total_benefits_total_covered_9_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_associate_professor_12_month ft_total_benefits_covered_associate_professor_12_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_instructor_12_month ft_total_benefits_covered_instructor_12_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_no_rank_12_month ft_total_benefits_covered_no_rank_12_month INT DEFAULT NULL, CHANGE ft_total_benefits_total_covered_12_month ft_total_benefits_total_covered_12_month INT DEFAULT NULL');*/
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP ft_salaries_9_month, DROP ft_salaries_12_month, DROP ft_average_total_compensation_professor_fica, DROP ft_average_total_compensation_associate_professor_fica, DROP ft_average_total_compensation_assistant_professor_fica, DROP ft_average_total_compensation_instructor_fica, DROP ft_average_total_compensation_lecturer_fica, DROP ft_average_total_compensation_no_rank_fica, DROP ft_average_total_compensation_all_ranks_fica, DROP ft_average_retirement_per_all_ranks, DROP ft_average_medical_per_all_ranks, DROP ft_average_dental_per_all_ranks, DROP ft_average_combined_medical_dental_per_all_ranks, DROP ft_average_disability_per_all_ranks, DROP ft_average_tuition_per_all_ranks, DROP ft_average_fica_per_all_ranks, DROP ft_average_unemployment_per_all_ranks, DROP ft_average_group_life_per_all_ranks, DROP ft_average_worker_comp_per_all_ranks, DROP ft_averge_other_per_all_ranks, DROP ft_average_all_combined_per_all_ranks, CHANGE ft_average_male_faculty_salary_9_month ft_average_male_faculty_salary_9_month INT DEFAULT NULL, CHANGE ft_average_female_faculty_salary_9_month ft_average_female_faculty_salary_9_month INT DEFAULT NULL, CHANGE ft_average_combined_faculty_salary_9_month ft_average_combined_faculty_salary_9_month INT DEFAULT NULL, CHANGE ft_average_male_faculty_salary_12_month ft_average_male_faculty_salary_12_month INT DEFAULT NULL, CHANGE ft_average_female_faculty_salary_12_month ft_average_female_faculty_salary_12_month INT DEFAULT NULL, CHANGE ft_average_combined_faculty_salary_12_month ft_average_combined_faculty_salary_12_month INT DEFAULT NULL, CHANGE ft_total_benefits_covered_associate_professor_9_month ft_total_benefits_covered_associate_professor_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_instructor_9_month ft_total_benefits_covered_instructor_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_no_rank_9_month ft_total_benefits_covered_no_rank_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_total_covered_9_month ft_total_benefits_total_covered_9_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_associate_professor_12_month ft_total_benefits_covered_associate_professor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_instructor_12_month ft_total_benefits_covered_instructor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_covered_no_rank_12_month ft_total_benefits_covered_no_rank_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_total_benefits_total_covered_12_month ft_total_benefits_total_covered_12_month DOUBLE PRECISION DEFAULT NULL');
    }
}
