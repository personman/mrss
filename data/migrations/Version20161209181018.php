<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209181018 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_drafts ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription_drafts ADD CONSTRAINT FK_25DCFC479A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_25DCFC479A1887DC ON subscription_drafts (subscription_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subscription_drafts DROP FOREIGN KEY FK_25DCFC479A1887DC');
        $this->addSql('DROP INDEX UNIQ_25DCFC479A1887DC ON subscription_drafts');
        $this->addSql('ALTER TABLE subscription_drafts DROP subscription_id');
    }
}
