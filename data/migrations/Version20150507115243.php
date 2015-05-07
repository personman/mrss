<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150507115243 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD max_res_pt_yminus3_perc_comp DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_yminus3_perc_transf DOUBLE PRECISION DEFAULT NULL, ADD max_res_pt_yminus3_perc_comp_transf DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP max_res_pt_yminus3_perc_comp, DROP max_res_pt_yminus3_perc_transf, DROP max_res_pt_yminus3_perc_comp_transf');
    }
}
