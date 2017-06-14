<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170526162515 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE outliers ADD system_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9D0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C2FFCDD9D0952FA5 ON outliers (system_id)');
        $this->addSql('ALTER TABLE reports ADD system_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745D0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F11FA745D0952FA5 ON reports (system_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9D0952FA5');
        $this->addSql('DROP INDEX IDX_C2FFCDD9D0952FA5 ON outliers');
        $this->addSql('ALTER TABLE outliers DROP system_id');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745D0952FA5');
        $this->addSql('DROP INDEX IDX_F11FA745D0952FA5 ON reports');
        $this->addSql('ALTER TABLE reports DROP system_id');
    }
}
