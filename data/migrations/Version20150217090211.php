<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150217090211 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations ADD ss_advising_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_counseling_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_career_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_financial_aid_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_tutoring_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_testing_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_disabserv_cost_per_contact DOUBLE PRECISION DEFAULT NULL, ADD ss_vetserv_cost_per_contact DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations DROP ss_advising_cost_per_contact, DROP ss_counseling_cost_per_contact, DROP ss_career_cost_per_contact, DROP ss_financial_aid_cost_per_contact, DROP ss_tutoring_cost_per_contact, DROP ss_testing_cost_per_contact, DROP ss_disabserv_cost_per_contact, DROP ss_vetserv_cost_per_contact');
    }
}
