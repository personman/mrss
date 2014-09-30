<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140930085147 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AA76ED395");
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AC09C807C");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AC09C807C FOREIGN KEY (impersonatingUser_id) REFERENCES users (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AA76ED395");
        $this->addSql("ALTER TABLE change_sets DROP FOREIGN KEY FK_F75A0C6AC09C807C");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AC09C807C FOREIGN KEY (impersonatingUser_id) REFERENCES users (id)");
    }
}
