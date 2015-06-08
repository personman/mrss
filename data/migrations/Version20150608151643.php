<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150608151643 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE report_items (id INT AUTO_INCREMENT NOT NULL, report_id INT NOT NULL, highlighted_college_id INT DEFAULT NULL, benchmark1_id INT DEFAULT NULL, benchmark2_id INT DEFAULT NULL, benchmark3_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, config LONGTEXT DEFAULT NULL, year INT DEFAULT NULL, cache LONGTEXT DEFAULT NULL, sequence INT NOT NULL, INDEX IDX_CB02EE344BD2A4C0 (report_id), INDEX IDX_CB02EE34323FEC4F (highlighted_college_id), INDEX IDX_CB02EE3411538835 (benchmark1_id), INDEX IDX_CB02EE343E627DB (benchmark2_id), INDEX IDX_CB02EE34BB5A40BE (benchmark3_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report_items ADD CONSTRAINT FK_CB02EE344BD2A4C0 FOREIGN KEY (report_id) REFERENCES reports (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_items ADD CONSTRAINT FK_CB02EE34323FEC4F FOREIGN KEY (highlighted_college_id) REFERENCES colleges (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_items ADD CONSTRAINT FK_CB02EE3411538835 FOREIGN KEY (benchmark1_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_items ADD CONSTRAINT FK_CB02EE343E627DB FOREIGN KEY (benchmark2_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_items ADD CONSTRAINT FK_CB02EE34BB5A40BE FOREIGN KEY (benchmark3_id) REFERENCES benchmarks (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE report_items');
    }
}
