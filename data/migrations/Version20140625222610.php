<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140625222610 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE peer_groups CHANGE states states LONGTEXT DEFAULT NULL, CHANGE environments environments VARCHAR(255) DEFAULT NULL, CHANGE workforceEnrollment workforceEnrollment VARCHAR(255) DEFAULT NULL, CHANGE workforceRevenue workforceRevenue VARCHAR(255) DEFAULT NULL, CHANGE serviceAreaPopulation serviceAreaPopulation VARCHAR(255) DEFAULT NULL, CHANGE serviceAreaUnemployment serviceAreaUnemployment VARCHAR(255) DEFAULT NULL, CHANGE serviceAreaMedianIncome serviceAreaMedianIncome VARCHAR(255) DEFAULT NULL, CHANGE institutionalType institutionalType VARCHAR(255) DEFAULT NULL, CHANGE institutionalControl institutionalControl VARCHAR(255) DEFAULT NULL, CHANGE facultyUnionized facultyUnionized VARCHAR(255) DEFAULT NULL, CHANGE staffUnionized staffUnionized VARCHAR(255) DEFAULT NULL, CHANGE ipedsFallEnrollment ipedsFallEnrollment VARCHAR(255) DEFAULT NULL, CHANGE fiscalCreditHours fiscalCreditHours VARCHAR(255) DEFAULT NULL, CHANGE pellGrantRecipients pellGrantRecipients VARCHAR(255) DEFAULT NULL, CHANGE operatingRevenue operatingRevenue VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE peer_groups CHANGE states states LONGTEXT NOT NULL, CHANGE environments environments VARCHAR(255) NOT NULL, CHANGE institutionalType institutionalType VARCHAR(255) NOT NULL, CHANGE institutionalControl institutionalControl VARCHAR(255) NOT NULL, CHANGE facultyUnionized facultyUnionized VARCHAR(255) NOT NULL, CHANGE staffUnionized staffUnionized VARCHAR(255) NOT NULL, CHANGE workforceEnrollment workforceEnrollment VARCHAR(255) NOT NULL, CHANGE workforceRevenue workforceRevenue VARCHAR(255) NOT NULL, CHANGE ipedsFallEnrollment ipedsFallEnrollment VARCHAR(255) NOT NULL, CHANGE fiscalCreditHours fiscalCreditHours VARCHAR(255) NOT NULL, CHANGE pellGrantRecipients pellGrantRecipients VARCHAR(255) NOT NULL, CHANGE operatingRevenue operatingRevenue VARCHAR(255) NOT NULL, CHANGE serviceAreaPopulation serviceAreaPopulation VARCHAR(255) NOT NULL, CHANGE serviceAreaUnemployment serviceAreaUnemployment VARCHAR(255) NOT NULL, CHANGE serviceAreaMedianIncome serviceAreaMedianIncome VARCHAR(255) NOT NULL");
    }
}
