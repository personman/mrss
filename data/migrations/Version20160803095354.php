<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160803095354 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE benchmarks ADD computeIfValuesMissing TINYINT(1) NOT NULL, ADD includeInOtherReports TINYINT(1) NOT NULL');
        $this->addSql('UPDATE benchmarks SET computeIfValuesMissing = FALSE');
        $this->addSql('UPDATE benchmarks SET includeInOtherReports = includeInNationalReport');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE benchmarks DROP computeIfValuesMissing, DROP includeInOtherReports');
    }
}
