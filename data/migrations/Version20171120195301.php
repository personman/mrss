<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171120195301 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE system_memberships DROP FOREIGN KEY FK_D9EA1C6E770124B2');
        $this->addSql('ALTER TABLE system_memberships DROP FOREIGN KEY FK_D9EA1C6ED0952FA5');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6E770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6ED0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE system_memberships DROP FOREIGN KEY FK_D9EA1C6E770124B2');
        $this->addSql('ALTER TABLE system_memberships DROP FOREIGN KEY FK_D9EA1C6ED0952FA5');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6E770124B2 FOREIGN KEY (college_id) REFERENCES colleges (id)');
        $this->addSql('ALTER TABLE system_memberships ADD CONSTRAINT FK_D9EA1C6ED0952FA5 FOREIGN KEY (system_id) REFERENCES college_systems (id)');
    }
}
