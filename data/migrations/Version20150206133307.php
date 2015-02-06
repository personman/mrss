<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150206133307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE peer_benchmarks (id INT AUTO_INCREMENT NOT NULL, benchmark_id INT DEFAULT NULL, college_id INT DEFAULT NULL, created DATETIME NOT NULL, INDEX IDX_4B2C13E5E80D127B (benchmark_id), INDEX IDX_4B2C13E5770124B2 (college_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE peer_benchmarks ADD CONSTRAINT FK_4B2C13E5E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE peer_benchmarks ADD CONSTRAINT FK_4B2C13E5770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP TABLE peer_benchmarks');
    }
}
