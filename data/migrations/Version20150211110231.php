<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150211110231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations ADD inst_cost_full_expend_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_full_expend_prof_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_cost_part_expend_prof_dev DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE sub_observations DROP inst_cost_full_expend_program_dev, DROP inst_cost_part_expend_program_dev, DROP inst_cost_full_expend_teaching, DROP inst_cost_part_expend_teaching, DROP inst_cost_full_expend_tutoring, DROP inst_cost_part_expend_tutoring, DROP inst_cost_full_expend_advising, DROP inst_cost_part_expend_advising, DROP inst_cost_full_expend_ac_service, DROP inst_cost_part_expend_ac_service, DROP inst_cost_full_expend_prof_dev, DROP inst_cost_part_expend_prof_dev');
    }
}
