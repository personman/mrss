<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120194334 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('set foreign_key_checks=0');
        $this->addSql('ALTER TABLE observations DROP FOREIGN KEY FK_BBC15BA8770124B2');
        $this->addSql('ALTER TABLE observations ADD CONSTRAINT FK_BBC15BA8770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE');
        $this->addSql('set foreign_key_checks=1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP FOREIGN KEY FK_BBC15BA8770124B2');
        $this->addSql('ALTER TABLE observations ADD CONSTRAINT FK_BBC15BA8770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9770124B2');
    }
}
