<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130416195435 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD ft_f_yminus4_headc VARCHAR(20) DEFAULT NULL, ADD ft_f_yminus4_degr_cert VARCHAR(20) DEFAULT NULL, ADD ft_perc_comp VARCHAR(20) DEFAULT NULL, ADD ft_f_yminus4_transf VARCHAR(20) DEFAULT NULL, ADD ft_perc_transf VARCHAR(20) DEFAULT NULL, ADD ft_perc_comp_transf VARCHAR(20) DEFAULT NULL, ADD pt_f_yminus4_headc VARCHAR(20) DEFAULT NULL, ADD pt_f_yminus4_degr_cert VARCHAR(20) DEFAULT NULL, ADD pt_perc_comp VARCHAR(20) DEFAULT NULL, ADD pt_f_yminus4_transf VARCHAR(20) DEFAULT NULL, ADD pt_perc_transf VARCHAR(20) DEFAULT NULL, ADD pt_perc_comp_transf VARCHAR(20) DEFAULT NULL, ADD f_yminus7_headc VARCHAR(20) DEFAULT NULL, ADD ft_yminus7_degr VARCHAR(20) DEFAULT NULL, ADD ft_yminus7_transf VARCHAR(20) DEFAULT NULL, ADD ft_minus7perc_comp VARCHAR(20) DEFAULT NULL, ADD percminus7_transf VARCHAR(20) DEFAULT NULL, ADD percminus7_comtran VARCHAR(20) DEFAULT NULL, ADD pt_fminus7_headc VARCHAR(20) DEFAULT NULL, ADD pt_yminus7_degr VARCHAR(20) DEFAULT NULL, ADD pt_yminus7_transf VARCHAR(20) DEFAULT NULL, ADD pt_perminus7_comp VARCHAR(20) DEFAULT NULL, ADD pt_percminus7_tran VARCHAR(20) DEFAULT NULL, ADD pt_pminus7_comtran VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP ft_f_yminus4_headc, DROP ft_f_yminus4_degr_cert, DROP ft_perc_comp, DROP ft_f_yminus4_transf, DROP ft_perc_transf, DROP ft_perc_comp_transf, DROP pt_f_yminus4_headc, DROP pt_f_yminus4_degr_cert, DROP pt_perc_comp, DROP pt_f_yminus4_transf, DROP pt_perc_transf, DROP pt_perc_comp_transf, DROP f_yminus7_headc, DROP ft_yminus7_degr, DROP ft_yminus7_transf, DROP ft_minus7perc_comp, DROP percminus7_transf, DROP percminus7_comtran, DROP pt_fminus7_headc, DROP pt_yminus7_degr, DROP pt_yminus7_transf, DROP pt_perminus7_comp, DROP pt_percminus7_tran, DROP pt_pminus7_comtran");
    }
}
