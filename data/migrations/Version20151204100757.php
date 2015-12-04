<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151204100757 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('ALTER TABLE observations CHANGE ft_percent_change_professor_standard ft_percent_change_professor_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_associate_professor_standard ft_percent_change_associate_professor_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_assistant_professor_standard ft_percent_change_assistant_professor_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_instructor_standard ft_percent_change_instructor_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_lecturer_standard ft_percent_change_lecturer_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_no_rank_standard ft_percent_change_no_rank_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_total_standard ft_percent_change_total_standard DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_professor_12_month ft_percent_change_professor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_associate_professor_12_month ft_percent_change_associate_professor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_assistant_professor_12_month ft_percent_change_assistant_professor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_instructor_12_month ft_percent_change_instructor_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_no_rank_12_month ft_percent_change_no_rank_12_month DOUBLE PRECISION DEFAULT NULL, CHANGE ft_percent_change_total_12_month ft_percent_change_total_12_month DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('ALTER TABLE observations CHANGE ft_percent_change_professor_standard ft_percent_change_professor_standard INT DEFAULT NULL, CHANGE ft_percent_change_associate_professor_standard ft_percent_change_associate_professor_standard INT DEFAULT NULL, CHANGE ft_percent_change_assistant_professor_standard ft_percent_change_assistant_professor_standard INT DEFAULT NULL, CHANGE ft_percent_change_instructor_standard ft_percent_change_instructor_standard INT DEFAULT NULL, CHANGE ft_percent_change_lecturer_standard ft_percent_change_lecturer_standard INT DEFAULT NULL, CHANGE ft_percent_change_no_rank_standard ft_percent_change_no_rank_standard INT DEFAULT NULL, CHANGE ft_percent_change_total_standard ft_percent_change_total_standard INT DEFAULT NULL, CHANGE ft_percent_change_professor_12_month ft_percent_change_professor_12_month INT DEFAULT NULL, CHANGE ft_percent_change_associate_professor_12_month ft_percent_change_associate_professor_12_month INT DEFAULT NULL, CHANGE ft_percent_change_assistant_professor_12_month ft_percent_change_assistant_professor_12_month INT DEFAULT NULL, CHANGE ft_percent_change_instructor_12_month ft_percent_change_instructor_12_month INT DEFAULT NULL, CHANGE ft_percent_change_no_rank_12_month ft_percent_change_no_rank_12_month INT DEFAULT NULL, CHANGE ft_percent_change_total_12_month ft_percent_change_total_12_month INT DEFAULT NULL');
    }
}
