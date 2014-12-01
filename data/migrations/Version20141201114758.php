<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141201114758 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations CHANGE group_form12_instw_cred_grad_enr_succ form12_instw_cred_grad_enr_succ DOUBLE PRECISION DEFAULT NULL, CHANGE group_form14b_serv_pop form14b_serv_pop VARCHAR(20) DEFAULT NULL');
        $this->addSql("UPDATE benchmarks SET dbColumn = 'form12_instw_cred_grad_enr_succ' WHERE dbColumn = 'group_form12_instw_cred_grad_enr_succ'");
        $this->addSql("UPDATE benchmarks SET dbColumn = 'form14b_serv_pop' WHERE dbColumn = 'group_form14b_serv_pop'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE observations CHANGE form12_instw_cred_grad_enr_succ group_form12_instw_cred_grad_enr_succ DOUBLE PRECISION DEFAULT NULL, CHANGE form14b_serv_pop group_form14b_serv_pop VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
