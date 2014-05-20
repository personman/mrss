<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140520135255 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD91409DD88");
        $this->addSql("DROP INDEX IDX_C2FFCDD91409DD88 ON outliers");
        $this->addSql("ALTER TABLE outliers ADD year INT NOT NULL, CHANGE observation_id study_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("CREATE INDEX IDX_C2FFCDD9E7B003E9 ON outliers (study_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9E7B003E9");
        $this->addSql("DROP INDEX IDX_C2FFCDD9E7B003E9 ON outliers");
        $this->addSql("ALTER TABLE outliers DROP year, CHANGE study_id observation_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD91409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id)");
        $this->addSql("CREATE INDEX IDX_C2FFCDD91409DD88 ON outliers (observation_id)");
    }
}
