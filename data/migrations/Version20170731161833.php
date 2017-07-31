<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170731161833 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE data_values DROP FOREIGN KEY FK_5A597DFF9A1887DC');
        $this->addSql('ALTER TABLE data_values ADD CONSTRAINT FK_5A597DFF9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE data_values DROP FOREIGN KEY FK_5A597DFF9A1887DC');
        $this->addSql('ALTER TABLE data_values ADD CONSTRAINT FK_5A597DFF9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
    }
}
