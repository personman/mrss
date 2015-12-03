<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151203110639 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE issues (id INT AUTO_INCREMENT NOT NULL, study_id INT DEFAULT NULL, college_id INT DEFAULT NULL, user_id INT DEFAULT NULL, year INT NOT NULL, value VARCHAR(255) DEFAULT NULL, message LONGTEXT DEFAULT NULL, errorCode VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, changeSet_id INT DEFAULT NULL, benchmarkGroup_id INT DEFAULT NULL, INDEX IDX_DA7D7F83E7B003E9 (study_id), INDEX IDX_DA7D7F83770124B2 (college_id), INDEX IDX_DA7D7F83C1BF59FF (changeSet_id), INDEX IDX_DA7D7F834F029208 (benchmarkGroup_id), INDEX IDX_DA7D7F83A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83C1BF59FF FOREIGN KEY (changeSet_id) REFERENCES change_sets (id)');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F834F029208 FOREIGN KEY (benchmarkGroup_id) REFERENCES benchmark_groups (id)');
        $this->addSql('ALTER TABLE issues ADD CONSTRAINT FK_DA7D7F83A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE issues');
    }
}
