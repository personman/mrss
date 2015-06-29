<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150626160008 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations CHANGE tot_fte_fac tot_fte_fac DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE peer_groups CHANGE year year INT DEFAULT NULL, CHANGE benchmarks benchmarks LONGTEXT DEFAULT NULL, CHANGE peers peers LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations CHANGE tot_fte_fac tot_fte_fac INT DEFAULT NULL');
        $this->addSql('ALTER TABLE peer_groups CHANGE year year INT NOT NULL, CHANGE benchmarks benchmarks LONGTEXT NOT NULL, CHANGE peers peers LONGTEXT NOT NULL');
    }
}
