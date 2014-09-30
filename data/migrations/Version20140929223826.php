<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140929223826 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFA770124B2");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFA770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE percentile_ranks DROP FOREIGN KEY FK_E9BB4DFA770124B2");
        $this->addSql("ALTER TABLE percentile_ranks ADD CONSTRAINT FK_E9BB4DFA770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
    }
}
