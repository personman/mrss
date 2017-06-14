<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170317155304 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE structures (id INT AUTO_INCREMENT NOT NULL, json LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE college_systems ADD dataEntryStructure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE college_systems ADD CONSTRAINT FK_934352B0AF33E124 FOREIGN KEY (dataEntryStructure_id) REFERENCES structures (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_934352B0AF33E124 ON college_systems (dataEntryStructure_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE college_systems DROP FOREIGN KEY FK_934352B0AF33E124');
        $this->addSql('DROP TABLE structures');
        $this->addSql('DROP INDEX UNIQ_934352B0AF33E124 ON college_systems');
        $this->addSql('ALTER TABLE college_systems DROP dataEntryStructure_id');
    }
}
