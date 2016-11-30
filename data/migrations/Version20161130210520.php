<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161130210520 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sections_benchmark_groups (section_id INT NOT NULL, benchmarkgroup_id INT NOT NULL, INDEX IDX_D4B85ED0D823E37A (section_id), INDEX IDX_D4B85ED0B674F05E (benchmarkgroup_id), PRIMARY KEY(section_id, benchmarkgroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sections_benchmark_groups ADD CONSTRAINT FK_D4B85ED0D823E37A FOREIGN KEY (section_id) REFERENCES study_sections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sections_benchmark_groups ADD CONSTRAINT FK_D4B85ED0B674F05E FOREIGN KEY (benchmarkgroup_id) REFERENCES benchmark_groups (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sections_benchmark_groups');
    }
}
