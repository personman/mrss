<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120195203 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups DROP FOREIGN KEY FK_CED87FA0770124B2');
        $this->addSql('DROP INDEX IDX_CED87FA0770124B2 ON peer_groups');
        $this->addSql('ALTER TABLE peer_groups ADD college INT NOT NULL, DROP college_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups ADD college_id INT DEFAULT NULL, DROP college');
        $this->addSql('ALTER TABLE peer_groups ADD CONSTRAINT FK_CED87FA0770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('CREATE INDEX IDX_CED87FA0770124B2 ON peer_groups (college_id)');
    }
}
