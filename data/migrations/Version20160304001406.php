<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160304001406 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD ft_total_compensation_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_associate_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_assistant_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_instructor DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_lecturer DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_no_rank DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_total_compensation_no_diff DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_associate_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_assistant_professor DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_instructor DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_lecturer DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_no_rank DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_all_ranks DOUBLE PRECISION DEFAULT NULL, ADD ft_average_total_compensation_no_diff DOUBLE PRECISION DEFAULT NULL, ADD ft_benefits_percent_salary DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP ft_total_compensation_professor, DROP ft_total_compensation_associate_professor, DROP ft_total_compensation_assistant_professor, DROP ft_total_compensation_instructor, DROP ft_total_compensation_lecturer, DROP ft_total_compensation_no_rank, DROP ft_total_compensation_all_ranks, DROP ft_total_compensation_no_diff, DROP ft_average_total_compensation_professor, DROP ft_average_total_compensation_associate_professor, DROP ft_average_total_compensation_assistant_professor, DROP ft_average_total_compensation_instructor, DROP ft_average_total_compensation_lecturer, DROP ft_average_total_compensation_no_rank, DROP ft_average_total_compensation_all_ranks, DROP ft_average_total_compensation_no_diff, DROP ft_benefits_percent_salary');
    }
}
