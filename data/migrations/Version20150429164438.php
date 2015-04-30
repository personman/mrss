<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150429164438 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD max_res_pt_perc_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_perc_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_perc_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_perc_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_perc_comp_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_perc_comp_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_minus7perc_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_percminus7_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_percminus7_comtran DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_perminus7_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_percminus7_tran DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_pminus7_comtran DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_yminus3_perc_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_yminus3_perc_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_ft_yminus3_perc_comp_transf DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP max_res_pt_perc_transf, DROP max_res_ft_perc_comp, DROP max_res_pt_perc_comp, DROP max_res_ft_perc_transf, DROP max_res_ft_perc_comp_transf, DROP max_res_pt_perc_comp_transf, DROP max_res_ft_minus7perc_comp, DROP max_res_percminus7_transf, DROP max_res_percminus7_comtran, DROP max_res_pt_perminus7_comp, DROP max_res_pt_percminus7_tran, DROP max_res_pt_pminus7_comtran, DROP max_res_ft_yminus3_perc_comp, DROP max_res_ft_yminus3_perc_transf, DROP max_res_ft_yminus3_perc_comp_transf');
    }
}
