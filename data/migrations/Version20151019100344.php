<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151019100344 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups DROP states, DROP environments, DROP workforceEnrollment, DROP workforceRevenue, DROP serviceAreaPopulation, DROP serviceAreaUnemployment, DROP serviceAreaMedianIncome, DROP institutionalType, DROP institutionalControl, DROP facultyUnionized, DROP staffUnionized, DROP ipedsFallEnrollment, DROP fiscalCreditHours, DROP pellGrantRecipients, DROP operatingRevenue, DROP blk, DROP asian, DROP hispAnyrace, DROP technicalCredit, DROP percentageFullTime, DROP onCampusHousing, DROP fourYearDegrees');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE peer_groups ADD states LONGTEXT DEFAULT NULL, ADD environments VARCHAR(255) DEFAULT NULL, ADD workforceEnrollment VARCHAR(255) DEFAULT NULL, ADD workforceRevenue VARCHAR(255) DEFAULT NULL, ADD serviceAreaPopulation VARCHAR(255) DEFAULT NULL, ADD serviceAreaUnemployment VARCHAR(255) DEFAULT NULL, ADD serviceAreaMedianIncome VARCHAR(255) DEFAULT NULL, ADD institutionalType VARCHAR(255) DEFAULT NULL, ADD institutionalControl VARCHAR(255) DEFAULT NULL, ADD facultyUnionized VARCHAR(255) DEFAULT NULL, ADD staffUnionized VARCHAR(255) DEFAULT NULL, ADD ipedsFallEnrollment VARCHAR(255) DEFAULT NULL, ADD fiscalCreditHours VARCHAR(255) DEFAULT NULL, ADD pellGrantRecipients VARCHAR(255) DEFAULT NULL, ADD operatingRevenue VARCHAR(255) DEFAULT NULL, ADD blk VARCHAR(255) DEFAULT NULL, ADD asian VARCHAR(255) DEFAULT NULL, ADD hispAnyrace VARCHAR(255) DEFAULT NULL, ADD technicalCredit VARCHAR(255) DEFAULT NULL, ADD percentageFullTime VARCHAR(255) DEFAULT NULL, ADD onCampusHousing VARCHAR(255) DEFAULT NULL, ADD fourYearDegrees VARCHAR(255) DEFAULT NULL');
    }
}
