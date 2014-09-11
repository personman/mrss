<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140911112825 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn IN ('ft_tot_stud_crhrs_tght', 'pt_tot_stud_crhrs_tght', 'full_time_credit_hours_percent', 'full_time_credit_hours', 'part_time_credit_hours_percent', 'part_time_credit_hours') AND benchmarkGroup_id = 40");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
