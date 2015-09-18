<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140916132857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO roles (parent_id, roleId) VALUES (2, 'viewer')");
        $this->addSql("UPDATE roles SET parent_id = 7 WHERE roleId = 'contact'");
        $this->addSql("UPDATE roles SET parent_id = 6 WHERE roleId = 'data'");
        $this->addSql("UPDATE roles SET parent_id = 5 WHERE roleId = 'system_admin'");
        $this->addSql("UPDATE roles SET parent_id = 5 WHERE roleId = 'admin'");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
