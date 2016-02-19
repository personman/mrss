<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160219095241 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE suppressions (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, benchmarkGroup_id INT DEFAULT NULL, INDEX IDX_92766F734F029208 (benchmarkGroup_id), INDEX IDX_92766F739A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE suppressions ADD CONSTRAINT FK_92766F734F029208 FOREIGN KEY (benchmarkGroup_id) REFERENCES benchmark_groups (id)');
        $this->addSql('ALTER TABLE suppressions ADD CONSTRAINT FK_92766F739A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE suppressions');
    }
}
