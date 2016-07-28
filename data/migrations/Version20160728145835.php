<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160728145835 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE percent_changes (id INT AUTO_INCREMENT NOT NULL, study_id INT DEFAULT NULL, college_id INT DEFAULT NULL, benchmark_id INT DEFAULT NULL, year INT NOT NULL, value VARCHAR(255) DEFAULT NULL, oldValue VARCHAR(255) DEFAULT NULL, percentChange DOUBLE PRECISION DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, staffNote LONGTEXT DEFAULT NULL, INDEX IDX_2E3D5571E7B003E9 (study_id), INDEX IDX_2E3D5571770124B2 (college_id), INDEX IDX_2E3D5571E80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE percent_changes ADD CONSTRAINT FK_2E3D5571E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('ALTER TABLE percent_changes ADD CONSTRAINT FK_2E3D5571770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('ALTER TABLE percent_changes ADD CONSTRAINT FK_2E3D5571E80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE percent_changes');
        $this->addSql('ALTER TABLE subscriptions CHANGE reportAccess reportAccess TINYINT(1) NOT NULL');
    }
}
