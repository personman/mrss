<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160101120726 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations CHANGE institution_sector institution_sector VARCHAR(255) DEFAULT NULL, CHANGE carnegie_basic carnegie_basic VARCHAR(255) DEFAULT NULL, CHANGE institution_highest_degree institution_highest_degree VARCHAR(255) DEFAULT NULL, CHANGE institution_grants_medical_degree institution_grants_medical_degree VARCHAR(255) DEFAULT NULL, CHANGE institution_eligible_cip_codes institution_eligible_cip_codes VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations CHANGE institution_sector institution_sector INT DEFAULT NULL, CHANGE carnegie_basic carnegie_basic INT DEFAULT NULL, CHANGE institution_highest_degree institution_highest_degree INT DEFAULT NULL, CHANGE institution_grants_medical_degree institution_grants_medical_degree INT DEFAULT NULL, CHANGE institution_eligible_cip_codes institution_eligible_cip_codes INT DEFAULT NULL');
    }
}
