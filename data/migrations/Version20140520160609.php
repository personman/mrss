<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140520160609 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers ADD college_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE outliers ADD CONSTRAINT FK_C2FFCDD9770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)");
        $this->addSql("CREATE INDEX IDX_C2FFCDD9770124B2 ON outliers (college_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE outliers DROP FOREIGN KEY FK_C2FFCDD9770124B2");
        $this->addSql("DROP INDEX IDX_C2FFCDD9770124B2 ON outliers");
        $this->addSql("ALTER TABLE outliers DROP college_id");
    }
}
