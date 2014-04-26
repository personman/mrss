<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140425212646 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE changes (id INT AUTO_INCREMENT NOT NULL, benchmark_id INT DEFAULT NULL, oldValue VARCHAR(255) NOT NULL, newValue VARCHAR(255) NOT NULL, changeSet_id INT DEFAULT NULL, INDEX IDX_2020B83DC1BF59FF (changeSet_id), INDEX IDX_2020B83DE80D127B (benchmark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE change_sets (id INT AUTO_INCREMENT NOT NULL, observation_id INT DEFAULT NULL, study_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date DATE DEFAULT NULL, impersonatingUser_id INT DEFAULT NULL, INDEX IDX_F75A0C6A1409DD88 (observation_id), INDEX IDX_F75A0C6AE7B003E9 (study_id), INDEX IDX_F75A0C6AA76ED395 (user_id), INDEX IDX_F75A0C6AC09C807C (impersonatingUser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE changes ADD CONSTRAINT FK_2020B83DC1BF59FF FOREIGN KEY (changeSet_id) REFERENCES change_sets (id)");
        $this->addSql("ALTER TABLE changes ADD CONSTRAINT FK_2020B83DE80D127B FOREIGN KEY (benchmark_id) REFERENCES benchmarks (id)");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6A1409DD88 FOREIGN KEY (observation_id) REFERENCES observations (id)");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AE7B003E9 FOREIGN KEY (study_id) REFERENCES studies (id)");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->addSql("ALTER TABLE change_sets ADD CONSTRAINT FK_F75A0C6AC09C807C FOREIGN KEY (impersonatingUser_id) REFERENCES users (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE changes DROP FOREIGN KEY FK_2020B83DC1BF59FF");
        $this->addSql("DROP TABLE changes");
        $this->addSql("DROP TABLE change_sets");
    }
}
