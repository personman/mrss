<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131030141836 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD op_exp_inst INT DEFAULT NULL, ADD op_exp_student_services INT DEFAULT NULL, ADD op_exp_acad_supp INT DEFAULT NULL, ADD op_exp_inst_support INT DEFAULT NULL, ADD op_exp_research INT DEFAULT NULL, ADD op_exp_pub_serv INT DEFAULT NULL, ADD op_exp_oper_n_maint INT DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP op_exp_inst, DROP op_exp_student_services, DROP op_exp_acad_supp, DROP op_exp_inst_support, DROP op_exp_research, DROP op_exp_pub_serv, DROP op_exp_oper_n_maint");
    }
}
