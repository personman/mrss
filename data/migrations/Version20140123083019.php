<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140123083019 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE offer_codes (id INT AUTO_INCREMENT NOT NULL, study_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_6680BCA4E7B003E9 (study_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE offer_codes ADD CONSTRAINT FK_6680BCA4E7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("ALTER TABLE studies DROP offerCodes, DROP offerCodePrice");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE offer_codes");
        $this->addSql("ALTER TABLE studies ADD offerCodes VARCHAR(255) DEFAULT NULL, ADD offerCodePrice DOUBLE PRECISION NOT NULL");
    }
}
