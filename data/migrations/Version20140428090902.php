<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140428090902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM percentiles");
        $this->addSql("DELETE FROM percentile_ranks");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_other_total_expend'");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_other_total_num'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
