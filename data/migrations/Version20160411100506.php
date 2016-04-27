<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160411100506 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE peer_groups ADD CONSTRAINT FK_CED87FA0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_CED87FA0A76ED395 ON peer_groups (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups DROP FOREIGN KEY FK_CED87FA0A76ED395');
        $this->addSql('DROP INDEX IDX_CED87FA0A76ED395 ON peer_groups');
        $this->addSql('ALTER TABLE peer_groups DROP user_id, DROP test');
    }
}
