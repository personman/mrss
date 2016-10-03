<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161003120044 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE change_sets ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6A9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F75A0C6A9A1887DC ON change_sets (subscription_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6A9A1887DC');
        $this->addSql('DROP INDEX IDX_F75A0C6A9A1887DC ON change_sets');
        $this->addSql('ALTER TABLE change_sets DROP subscription_id');
    }
}
