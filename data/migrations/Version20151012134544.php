<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151012134544 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE criteria (id INT AUTO_INCREMENT NOT NULL, study_id INT DEFAULT NULL, benchmark_id INT DEFAULT NULL, sequence INT NOT NULL, name VARCHAR(255) NOT NULL, helpText VARCHAR(255) NOT NULL, INDEX IDX_B61F9B81E7B003E9 (study_id), INDEX IDX_B61F9B81E80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE criteria');
    }
}
