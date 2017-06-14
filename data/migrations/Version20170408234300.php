<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170408234300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE college_systems ADD reportStructure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE college_systems ADD CONSTRAINT FK_934352B0D2A0C285 FOREIGN KEY (reportStructure_id) REFERENCES structures (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_934352B0D2A0C285 ON college_systems (reportStructure_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE college_systems DROP FOREIGN KEY FK_934352B0D2A0C285');
        $this->addSql('DROP INDEX UNIQ_934352B0D2A0C285 ON college_systems');
        $this->addSql('ALTER TABLE college_systems DROP reportStructure_id');
    }
}
