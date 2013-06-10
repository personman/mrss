<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130610144235 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // Add the main users
        $this->addSql("INSERT INTO users (id, email, displayName, password, role) VALUES (1, 'dfergu15@jccc.edu', 'Danny Ferguson', '\$2y\$14\$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC', 'admin') ON DUPLICATE KEY UPDATE id=id;");

        // Patrick password: uwjdjfw
        $this->addSql("INSERT INTO users (id, email, displayName, password, role) VALUES (2, 'prossol@jccc.edu', 'Patrick Rossol-Allison', '\$2y\$14\$Hkf5od5XbZ8Ltg8DSZmU4OoSyg3JaCad.lKsayqQCWi.efpiINkbu', 'admin') ON DUPLICATE KEY UPDATE id=id;");

        // Victoria password: ekejfksod
        $this->addSql("INSERT INTO users (id, email, displayName, password, role) VALUES (3, 'vdouglas@jccc.edu', 'Victoria Douglas', '\$2y\$14\$bMqzS8Sq3r.tKHpEMzPoZeYx4sGGc094P3ttPIy3Jqobv0astD4Au', 'admin') ON DUPLICATE KEY UPDATE id=id;");

        // Michelle password: wlekdjfgdlsk
        $this->addSql("INSERT INTO users (id, email, displayName, password, role) VALUES (4, 'mtaylo24@jccc.edu', 'Michelle Taylor', '\$2y\$14\$ytv2PhoclJ9qv6GvIJf.kecqQrtitfcnpbC1ufTG5zvrZ7ZVQsb3G', 'admin') ON DUPLICATE KEY UPDATE id=id;");


        // Add about page
        $content = $this->loadContent('mrss.html');
        $this->addSql("INSERT INTO pages (id, title, route, status, content) VALUES (6, 'About Maximizing Resources for Student Success', 'mrss', 'published', '$content') ON DUPLICATE KEY UPDATE id=id");

        // Add staff page
        $content = $this->loadContent('staff.html');
        $this->addSql("INSERT INTO pages (id, title, route, status, content) VALUES (5, 'Our Staff', 'staff', 'published', '$content') ON DUPLICATE KEY UPDATE id=id");

        // Add testimonials page
        $content = $this->loadContent('testimonials.html');
        $this->addSql("INSERT INTO pages (id, title, route, status, content) VALUES (7, 'Testimonials', 'testimonials', 'published', '$content') ON DUPLICATE KEY UPDATE id=id");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DELETE FROM pages WHERE id = 5");
        $this->addSql("DELETE FROM pages WHERE id = 6");
        $this->addSql("DELETE FROM pages WHERE id = 7");

    }

    public function loadContent($name)
    {
        $dir = dirname(__FILE__);
        $fullname = $dir . '/' . $name;

        return file_get_contents($fullname);
    }
}
