<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150910135421 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups ADD study_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE peer_groups ADD CONSTRAINT FK_CED87FA0E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('CREATE INDEX IDX_CED87FA0E7B003E9 ON peer_groups (study_id)');
        $this->addSql('UPDATE peer_groups SET study_id = 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups DROP FOREIGN KEY FK_CED87FA0E7B003E9');
        $this->addSql('DROP INDEX IDX_CED87FA0E7B003E9 ON peer_groups');
        $this->addSql('ALTER TABLE peer_groups DROP study_id');
    }
}
