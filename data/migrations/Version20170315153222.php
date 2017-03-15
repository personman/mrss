<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170315153222 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE system_memberships (id INT AUTO_INCREMENT NOT NULL, college_id INT DEFAULT NULL, system_id INT DEFAULT NULL, year INT NOT NULL, dataVisibility VARCHAR(255) NOT NULL, INDEX IDX_D9EA1C6E770124B2 (college_id), INDEX IDX_D9EA1C6ED0952FA5 (system_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6E770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6ED0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id)');

        $this->addSql('insert into system_memberships (college_id, system_id, year)
select c.id college_id, system_id, s.year
from colleges c
inner join subscriptions s on s.college_id = c.id
where system_id is not null;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE system_memberships');
    }
}
