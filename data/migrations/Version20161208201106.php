<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161208201106 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS subscription_sections (subscription_id INT NOT NULL, section_id INT NOT NULL, INDEX IDX_4C381CB49A1887DC (subscription_id), INDEX IDX_4C381CB4D823E37A (section_id), PRIMARY KEY(subscription_id, section_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0; ALTER TABLE subscription_sections ADD CONSTRAINT FK_4C381CB49A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0; ALTER TABLE subscription_sections ADD CONSTRAINT FK_4C381CB4D823E37A FOREIGN KEY (section_id) REFERENCES study_sections (id) ON DELETE CASCADE; SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE subscription_sections');
    }
}
