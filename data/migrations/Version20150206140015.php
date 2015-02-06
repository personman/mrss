<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150206140015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE peer_benchmarks ADD study_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE peer_benchmarks ADD CONSTRAINT FK_4B2C13E5E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('CREATE INDEX IDX_4B2C13E5E7B003E9 ON peer_benchmarks (study_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE peer_benchmarks DROP FOREIGN KEY FK_4B2C13E5E7B003E9');
        $this->addSql('DROP INDEX IDX_4B2C13E5E7B003E9 ON peer_benchmarks');
        $this->addSql('ALTER TABLE peer_benchmarks DROP study_id');
    }
}
