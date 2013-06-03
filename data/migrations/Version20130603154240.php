<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130603154240 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD tot_ft_reg_empl VARCHAR(20) DEFAULT NULL, ADD ret VARCHAR(20) DEFAULT NULL, ADD dep VARCHAR(20) DEFAULT NULL, ADD ret_occ_rate VARCHAR(20) DEFAULT NULL, ADD dep_occ_rate VARCHAR(20) DEFAULT NULL, ADD tot_empl VARCHAR(20) DEFAULT NULL, ADD griev VARCHAR(20) DEFAULT NULL, ADD harass VARCHAR(20) DEFAULT NULL, ADD griev_occ_rate VARCHAR(20) DEFAULT NULL, ADD harass_occ_rate VARCHAR(20) DEFAULT NULL, ADD tot_dir_exp VARCHAR(20) DEFAULT NULL, ADD tot_fy_stud_crh VARCHAR(20) DEFAULT NULL, ADD tot_fte_stud VARCHAR(20) DEFAULT NULL, ADD cst_crh VARCHAR(20) DEFAULT NULL, ADD cst_fte_stud VARCHAR(20) DEFAULT NULL, ADD tot_dev_train_exp VARCHAR(20) DEFAULT NULL, ADD tot_fte_cred_fac VARCHAR(20) DEFAULT NULL, ADD tot_fte_staff VARCHAR(20) DEFAULT NULL, ADD tot_fte_empl VARCHAR(20) DEFAULT NULL, ADD exp_fte_empl VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP tot_ft_reg_empl, DROP ret, DROP dep, DROP ret_occ_rate, DROP dep_occ_rate, DROP tot_empl, DROP griev, DROP harass, DROP griev_occ_rate, DROP harass_occ_rate, DROP tot_dir_exp, DROP tot_fy_stud_crh, DROP tot_fte_stud, DROP cst_crh, DROP cst_fte_stud, DROP tot_dev_train_exp, DROP tot_fte_cred_fac, DROP tot_fte_staff, DROP tot_fte_empl, DROP exp_fte_empl");
    }
}
