<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140501145653 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_full_total_expend, DROP inst_part_total_expend, DROP inst_full_total_num, DROP inst_part_total_num");
        $this->addSql("ALTER TABLE peer_groups ADD institutionalType VARCHAR(255) NOT NULL, ADD institutionalControl VARCHAR(255) NOT NULL, ADD facultyUnionized VARCHAR(255) NOT NULL, ADD staffUnionized VARCHAR(255) NOT NULL, ADD ipedsFallEnrollment VARCHAR(255) NOT NULL, ADD fiscalCreditHours VARCHAR(255) NOT NULL, ADD pellGrantRecipients VARCHAR(255) NOT NULL, ADD operatingRevenue VARCHAR(255) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_full_total_expend DOUBLE PRECISION DEFAULT NULL, ADD inst_part_total_expend DOUBLE PRECISION DEFAULT NULL, ADD inst_full_total_num INT DEFAULT NULL, ADD inst_part_total_num INT DEFAULT NULL");
        $this->addSql("ALTER TABLE peer_groups DROP institutionalType, DROP institutionalControl, DROP facultyUnionized, DROP staffUnionized, DROP ipedsFallEnrollment, DROP fiscalCreditHours, DROP pellGrantRecipients, DROP operatingRevenue");
    }
}
