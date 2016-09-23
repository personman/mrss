<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160318144207 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE data_values (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, benchmark_id INT DEFAULT NULL, floatValue DOUBLE PRECISION DEFAULT NULL, stringValue LONGTEXT DEFAULT NULL, INDEX IDX_5A597DFF9A1887DC (subscription_id), INDEX IDX_5A597DFFE80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE data_values ADD CONSTRAINT FK_5A597DFF9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
        $this->addSql('ALTER TABLE data_values ADD CONSTRAINT FK_5A597DFFE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE data_values');
    }
}
