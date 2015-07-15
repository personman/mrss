<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150715143631 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD ft_minus4_perc_completed DOUBLE PRECISION DEFAULT NULL, ADD ft_minus4_perc_transferred DOUBLE PRECISION DEFAULT NULL, ADD ft_minus4_perc_comp_or_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_minus4_perc_comp_and_transf DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP ft_minus4_perc_completed, DROP ft_minus4_perc_transferred, DROP ft_minus4_perc_comp_or_transf, DROP ft_minus4_perc_comp_and_transf');
    }
}
