<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140316123418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentiles ADD study_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE percentiles ADD CONSTRAINT FK_131D1EEEE7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("CREATE INDEX IDX_131D1EEEE7B003E9 ON percentiles (study_id)");
        $this->addSql("ALTER TABLE percentile_ranks ADD study_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFAE7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("CREATE INDEX IDX_E9BB4DFAE7B003E9 ON percentile_ranks (study_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFAE7B003E9");
        $this->addSql("DROP INDEX IDX_E9BB4DFAE7B003E9 ON percentile_ranks");
        $this->addSql("ALTER TABLE percentile_ranks DROP study_id");
        $this->addSql("ALTER TABLE percentiles DROP FOREIGN KEY FK_131D1EEEE7B003E9");
        $this->addSql("DROP INDEX IDX_131D1EEEE7B003E9 ON percentiles");
        $this->addSql("ALTER TABLE percentiles DROP study_id");
        $this->addSql("DROP INDEX idx_4778a011409dd88 ON subscriptions");
    }
}
