<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416200257 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD tot_cr_st VARCHAR(20) DEFAULT NULL, ADD grad_bef_spr VARCHAR(20) DEFAULT NULL, ADD enr_bef_spr VARCHAR(20) DEFAULT NULL, ADD enr_fall VARCHAR(20) DEFAULT NULL, ADD grad_bef_fall VARCHAR(20) DEFAULT NULL, ADD next_term_pers VARCHAR(20) DEFAULT NULL, ADD fall_fall_pers VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP tot_cr_st, DROP grad_bef_spr, DROP enr_bef_spr, DROP enr_fall, DROP grad_bef_fall, DROP next_term_pers, DROP fall_fall_pers");
    }
}
