<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140919231954 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE benchmarks ADD reportLabel VARCHAR(255) DEFAULT NULL, ADD reportWeight INT DEFAULT NULL, ADD peerReportLabel VARCHAR(255) DEFAULT NULL, ADD descriptiveReportLabel VARCHAR(255) DEFAULT NULL, ADD yearOffset INT DEFAULT NULL, ADD yearPrefix VARCHAR(255) DEFAULT NULL, ADD includeInBestPerformer TINYINT(1) NOT NULL, ADD highIsBetter TINYINT(1) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE benchmarks DROP reportLabel, DROP reportWeight, DROP peerReportLabel, DROP descriptiveReportLabel, DROP yearOffset, DROP yearPrefix, DROP includeInBestPerformer, DROP highIsBetter");
    }
}
