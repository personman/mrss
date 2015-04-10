<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150410150230 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD ft_f_yminus3_headc VARCHAR(20) DEFAULT NULL, ADD ft_f_yminus3_degr_cert VARCHAR(20) DEFAULT NULL, ADD ft_f_yminus3_degr_and_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_yminus3_perc_comp VARCHAR(20) DEFAULT NULL, ADD ft_f_yminus3_transf VARCHAR(20) DEFAULT NULL, ADD ft_yminus3_perc_transf VARCHAR(20) DEFAULT NULL, ADD ft_yminus3_perc_comp_transf VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP ft_f_yminus3_headc, DROP ft_f_yminus3_degr_cert, DROP ft_f_yminus3_degr_and_transf, DROP ft_yminus3_perc_comp, DROP ft_f_yminus3_transf, DROP ft_yminus3_perc_transf, DROP ft_yminus3_perc_comp_transf');
    }
}
