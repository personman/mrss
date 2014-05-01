<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140501144318 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Delete some pilot benchmarks
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_full_total_expend'");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_part_total_expend'");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_full_total_num'");
        $this->addSql("DELETE FROM benchmarks WHERE dbColumn = 'inst_part_total_num'");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
