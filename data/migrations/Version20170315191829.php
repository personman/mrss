<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170315191829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE colleges DROP FOREIGN KEY FK_F5AA74A0D0952FA5');
        $this->addSql('DROP INDEX IDX_F5AA74A0D0952FA5 ON colleges');
        $this->addSql('ALTER TABLE colleges DROP system_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE colleges ADD system_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE colleges ADD CONSTRAINT FK_F5AA74A0D0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id)');
        $this->addSql('CREATE INDEX IDX_F5AA74A0D0952FA5 ON colleges (system_id)');
    }
}
