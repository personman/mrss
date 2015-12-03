<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151203133153 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE issues DROP FOREIGN KEY FK_DA7D7F834F029208');
        $this->addSql('DROP INDEX IDX_DA7D7F834F029208 ON issues');
        $this->addSql('ALTER TABLE issues ADD formUrl VARCHAR(255) DEFAULT NULL, DROP benchmarkGroup_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE issues ADD benchmarkGroup_id INT DEFAULT NULL, DROP formUrl');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F834F029208 FOREIGN KEY (benchmarkGroup_id) REFERENCES benchmark_groups (id)');
        $this->addSql('CREATE INDEX IDX_DA7D7F834F029208 ON issues (benchmarkGroup_id)');
    }
}
