<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150422104208 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE charts (id INT AUTO_INCREMENT NOT NULL, study_id INT DEFAULT NULL, college_id INT DEFAULT NULL, highlighted_college_id INT DEFAULT NULL, benchmark1_id INT DEFAULT NULL, benchmark2_id INT DEFAULT NULL, benchmark3_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, config LONGTEXT DEFAULT NULL, year INT NOT NULL, INDEX IDX_C05050F7E7B003E9 (study_id), INDEX IDX_C05050F7770124B2 (college_id), INDEX IDX_C05050F7323FEC4F (highlighted_college_id), INDEX IDX_C05050F711538835 (benchmark1_id), INDEX IDX_C05050F73E627DB (benchmark2_id), INDEX IDX_C05050F7BB5A40BE (benchmark3_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F7E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F7770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F7323FEC4F FOREIGN KEY (highlighted_college_id) REFERENCES colleges (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F711538835 FOREIGN KEY (benchmark1_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F73E627DB FOREIGN KEY (benchmark2_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE charts ADD CONSTRAINT FK_C05050F7BB5A40BE FOREIGN KEY (benchmark3_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP TABLE charts');
    }
}
