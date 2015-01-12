<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150112172038 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD on_campus_housing LONGTEXT DEFAULT NULL, ADD four_year_degrees LONGTEXT DEFAULT NULL, ADD ft_f_yminus4_degr_and_transf INT DEFAULT NULL, ADD pt_f_yminus4_degr_and_transf INT DEFAULT NULL, ADD ft_yminus7_degr_and_tranf VARCHAR(20) DEFAULT NULL, ADD pt_yminus7_degr_and_tranf VARCHAR(20) DEFAULT NULL, ADD tot_stud_trans_two_year VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP on_campus_housing, DROP four_year_degrees, DROP ft_f_yminus4_degr_and_transf, DROP pt_f_yminus4_degr_and_transf, DROP ft_yminus7_degr_and_tranf, DROP pt_yminus7_degr_and_tranf, DROP tot_stud_trans_two_year');
    }
}
