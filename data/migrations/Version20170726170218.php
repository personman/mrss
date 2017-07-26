<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170726170218 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE issues DROP FOREIGN KEY FK_DA7D7F83770124B2');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE issues DROP FOREIGN KEY FK_DA7D7F83770124B2');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
    }
}
