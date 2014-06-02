<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140601195943 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO roles (id, parent_id, roleId) VALUES (5, 1, 'data')");
        $this->addSql("INSERT INTO roles (id, parent_id, roleId) VALUES (6, 1, 'contact')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM roles WHERE roleId = 'data')");
        $this->addSql("DELETE FROM roles WHERE roleId = 'contact')");
    }
}
