<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130731221315 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE pages_studies (page_id INT NOT NULL, study_id INT NOT NULL, INDEX IDX_53BB8DD5C4663E4 (page_id), INDEX IDX_53BB8DD5E7B003E9 (study_id), PRIMARY KEY(page_id, study_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE pages_studies ADD CONSTRAINT FK_53BB8DD5C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE pages_studies ADD CONSTRAINT FK_53BB8DD5E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE pages_studies");
    }
}
