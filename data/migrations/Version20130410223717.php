<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130410223717 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD unemp_rate VARCHAR(20) DEFAULT NULL, ADD med_hhold_inc VARCHAR(20) DEFAULT NULL, ADD ft_cr_head VARCHAR(20) DEFAULT NULL, ADD pt_cr_head VARCHAR(20) DEFAULT NULL, ADD trans_cred VARCHAR(20) DEFAULT NULL, ADD t_c_crh VARCHAR(20) DEFAULT NULL, ADD dev_crh VARCHAR(20) DEFAULT NULL, ADD crd_stud_minc VARCHAR(20) DEFAULT NULL, ADD fem_cred_stud VARCHAR(20) DEFAULT NULL, ADD non_res_alien VARCHAR(20) DEFAULT NULL, ADD blk_n_hisp VARCHAR(20) DEFAULT NULL, ADD ind_alaska VARCHAR(20) DEFAULT NULL, ADD asia_pacif VARCHAR(20) DEFAULT NULL, ADD hisp VARCHAR(20) DEFAULT NULL, ADD wht_n_hisp VARCHAR(20) DEFAULT NULL, ADD race_eth_unk VARCHAR(20) DEFAULT NULL, ADD tuition_fees VARCHAR(20) DEFAULT NULL, ADD unre_o_rev VARCHAR(20) DEFAULT NULL, ADD loc_sour VARCHAR(20) DEFAULT NULL, ADD state_sour VARCHAR(20) DEFAULT NULL, ADD tuition_fees_sour VARCHAR(20) DEFAULT NULL, ADD total_pop VARCHAR(20) DEFAULT NULL, ADD ipeds_enr VARCHAR(20) DEFAULT NULL, ADD non_cr_hdct VARCHAR(20) DEFAULT NULL, ADD non_res_alien_2012 VARCHAR(20) DEFAULT NULL, ADD race_eth_unk_2012 VARCHAR(20) DEFAULT NULL, ADD hisp_anyrace_2012 VARCHAR(20) DEFAULT NULL, ADD ind_alaska_2012 VARCHAR(20) DEFAULT NULL, ADD asian_2012 VARCHAR(20) DEFAULT NULL, ADD blk_2012 VARCHAR(20) DEFAULT NULL, ADD haw_pacific_2012 VARCHAR(20) DEFAULT NULL, ADD white_2012 VARCHAR(20) DEFAULT NULL, ADD two_or_more VARCHAR(20) DEFAULT NULL, ADD hs_stud_crh VARCHAR(20) DEFAULT NULL, ADD pell_grant_rec VARCHAR(20) DEFAULT NULL, ADD hs_stud_hdct VARCHAR(20) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP unemp_rate, DROP med_hhold_inc, DROP ft_cr_head, DROP pt_cr_head, DROP trans_cred, DROP t_c_crh, DROP dev_crh, DROP crd_stud_minc, DROP fem_cred_stud, DROP non_res_alien, DROP blk_n_hisp, DROP ind_alaska, DROP asia_pacif, DROP hisp, DROP wht_n_hisp, DROP race_eth_unk, DROP tuition_fees, DROP unre_o_rev, DROP loc_sour, DROP state_sour, DROP tuition_fees_sour, DROP total_pop, DROP ipeds_enr, DROP non_cr_hdct, DROP non_res_alien_2012, DROP race_eth_unk_2012, DROP hisp_anyrace_2012, DROP ind_alaska_2012, DROP asian_2012, DROP blk_2012, DROP haw_pacific_2012, DROP white_2012, DROP two_or_more, DROP hs_stud_crh, DROP pell_grant_rec, DROP hs_stud_hdct");
    }
}
