<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416202242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ac_adv_coun VARCHAR(20) DEFAULT NULL, ADD ac_serv VARCHAR(20) DEFAULT NULL, ADD adm_fin_aid VARCHAR(20) DEFAULT NULL, ADD camp_clim VARCHAR(20) DEFAULT NULL, ADD camp_supp VARCHAR(20) DEFAULT NULL, ADD conc_indiv VARCHAR(20) DEFAULT NULL, ADD instr_eff VARCHAR(20) DEFAULT NULL, ADD reg_eff VARCHAR(20) DEFAULT NULL, ADD resp_div_pop VARCHAR(20) DEFAULT NULL, ADD safe_sec VARCHAR(20) DEFAULT NULL, ADD serv_exc VARCHAR(20) DEFAULT NULL, ADD stud_centr VARCHAR(20) DEFAULT NULL, ADD act_coll_learn VARCHAR(20) DEFAULT NULL, ADD stud_eff VARCHAR(20) DEFAULT NULL, ADD acad_chall VARCHAR(20) DEFAULT NULL, ADD stud_fac_int VARCHAR(20) DEFAULT NULL, ADD sup_learn VARCHAR(20) DEFAULT NULL, ADD choo_again VARCHAR(20) DEFAULT NULL, ADD ova_impr VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ac_adv_coun, DROP ac_serv, DROP adm_fin_aid, DROP camp_clim, DROP camp_supp, DROP conc_indiv, DROP instr_eff, DROP reg_eff, DROP resp_div_pop, DROP safe_sec, DROP serv_exc, DROP stud_centr, DROP act_coll_learn, DROP stud_eff, DROP acad_chall, DROP stud_fac_int, DROP sup_learn, DROP choo_again, DROP ova_impr");
    }
}
