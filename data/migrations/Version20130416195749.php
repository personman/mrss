<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416195749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD tot_stud_trans VARCHAR(20) DEFAULT NULL, ADD fst_yr_gpa VARCHAR(20) DEFAULT NULL, ADD tot_fst_yr_crh VARCHAR(20) DEFAULT NULL, ADD enro_next_yr VARCHAR(20) DEFAULT NULL, ADD avrg_1y_crh VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP tot_stud_trans, DROP fst_yr_gpa, DROP tot_fst_yr_crh, DROP enro_next_yr, DROP avrg_1y_crh");
    }
}
