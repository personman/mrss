<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160112161728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD pell_grant_eligble DOUBLE PRECISION DEFAULT NULL, ADD ft_f_yminus3_degr_not_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_f_yminus4_degr_not_transf DOUBLE PRECISION DEFAULT NULL, ADD pt_f_yminus4_degr_not_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_f_yminus7_degr_not_transf DOUBLE PRECISION DEFAULT NULL, ADD pt_f_yminus7_degr_not_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_cr_st DOUBLE PRECISION DEFAULT NULL, ADD ft_grad_bef_spr DOUBLE PRECISION DEFAULT NULL, ADD ft_enr_bef_spr DOUBLE PRECISION DEFAULT NULL, ADD ft_next_term_pers DOUBLE PRECISION DEFAULT NULL, ADD ft_grad_bef_fall DOUBLE PRECISION DEFAULT NULL, ADD ft_enr_fall DOUBLE PRECISION DEFAULT NULL, ADD ft_fall_fall_pers DOUBLE PRECISION DEFAULT NULL, ADD pt_cr_st DOUBLE PRECISION DEFAULT NULL, ADD pt_grad_bef_spr DOUBLE PRECISION DEFAULT NULL, ADD pt_enr_bef_spr DOUBLE PRECISION DEFAULT NULL, ADD pt_next_term_pers DOUBLE PRECISION DEFAULT NULL, ADD pt_grad_bef_fall DOUBLE PRECISION DEFAULT NULL, ADD pt_enr_fall DOUBLE PRECISION DEFAULT NULL, ADD pt_fall_fall_pers DOUBLE PRECISION DEFAULT NULL, ADD gm_abcpdfw DOUBLE PRECISION DEFAULT NULL, ADD gm_abcpdf DOUBLE PRECISION DEFAULT NULL, ADD gm_abcp DOUBLE PRECISION DEFAULT NULL, ADD gm_retention_rate DOUBLE PRECISION DEFAULT NULL, ADD gm_enr_suc_rate DOUBLE PRECISION DEFAULT NULL, ADD gm_comp_suc_rate DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP pell_grant_eligble, DROP ft_f_yminus3_degr_not_transf, DROP ft_f_yminus4_degr_not_transf, DROP pt_f_yminus4_degr_not_transf, DROP ft_f_yminus7_degr_not_transf, DROP pt_f_yminus7_degr_not_transf, DROP ft_cr_st, DROP ft_grad_bef_spr, DROP ft_enr_bef_spr, DROP ft_next_term_pers, DROP ft_grad_bef_fall, DROP ft_enr_fall, DROP ft_fall_fall_pers, DROP pt_cr_st, DROP pt_grad_bef_spr, DROP pt_enr_bef_spr, DROP pt_next_term_pers, DROP pt_grad_bef_fall, DROP pt_enr_fall, DROP pt_fall_fall_pers, DROP gm_abcpdfw, DROP gm_abcpdf, DROP gm_abcp, DROP gm_retention_rate, DROP gm_enr_suc_rate, DROP gm_comp_suc_rate');
    }
}
