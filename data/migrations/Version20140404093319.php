<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140404093319 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations CHANGE ss_counceling ss_counseling DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counceling_total ss_counseling_total DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counceling_contract ss_counseling_contract DOUBLE PRECISION DEFAULT NULL");
        $this->addSql("UPDATE benchmarks SET dbColumn = 'ss_counseling_total' WHERE dbColumn = 'ss_counceling_total'");
        $this->addSql("UPDATE benchmarks SET dbColumn = 'ss_counseling' WHERE dbColumn = 'ss_counceling'");
        $this->addSql("UPDATE benchmarks SET dbColumn = 'ss_counseling_contract' WHERE dbColumn = 'ss_counceling_contract'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations CHANGE ss_counseling ss_counceling DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counseling_total ss_counceling_total DOUBLE PRECISION DEFAULT NULL, CHANGE ss_counseling_contract ss_counceling_contract DOUBLE PRECISION DEFAULT NULL");
    }
}
