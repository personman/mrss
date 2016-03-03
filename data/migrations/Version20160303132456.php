<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160303132456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*$this->addSql('ALTER TABLE observations ADD ft_total_benefits_expenditure_professor_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expenditure_associate_professor_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expenditure_assistant_professor_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expenditure_instructor_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expenditure_lecturer_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expenditure_no_rank_converted DOUBLE PRECISION DEFAULT NULL, ADD ft_total_benefits_expentirue_no_diff_converted DOUBLE PRECISION DEFAULT NULL');*/
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP ft_total_benefits_expenditure_professor_converted, DROP ft_total_benefits_expenditure_associate_professor_converted, DROP ft_total_benefits_expenditure_assistant_professor_converted, DROP ft_total_benefits_expenditure_instructor_converted, DROP ft_total_benefits_expenditure_lecturer_converted, DROP ft_total_benefits_expenditure_no_rank_converted, DROP ft_total_benefits_expentirue_no_diff_converted');
    }
}
