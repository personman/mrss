<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141007111144 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentiles ADD system_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE percentiles ADD CONSTRAINT FK_131D1EEED0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_131D1EEED0952FA5 ON percentiles (system_id)");
        $this->addSql("ALTER TABLE percentile_ranks ADD system_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFAD0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_E9BB4DFAD0952FA5 ON percentile_ranks (system_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFAD0952FA5");
        $this->addSql("DROP INDEX IDX_E9BB4DFAD0952FA5 ON percentile_ranks");
        $this->addSql("ALTER TABLE percentile_ranks DROP system_id");
        $this->addSql("ALTER TABLE percentiles DROP FOREIGN KEY FK_131D1EEED0952FA5");
        $this->addSql("DROP INDEX IDX_131D1EEED0952FA5 ON percentiles");
        $this->addSql("ALTER TABLE percentiles DROP system_id");
    }
}
