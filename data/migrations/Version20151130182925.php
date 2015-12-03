<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151130182925 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*$this->addSql('ALTER TABLE observations DROP max_res_male_cred_stud, DROP max_res_op_exp_inst, DROP max_res_op_exp_student_services, DROP max_res_op_exp_acad_supp, DROP max_res_op_exp_inst_support, DROP max_res_op_exp_research, DROP max_res_op_exp_pub_serv, DROP max_res_op_exp_oper_n_maint');*/
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD max_res_male_cred_stud DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_inst DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_student_services DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_acad_supp DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_inst_support DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_research DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_pub_serv DOUBLE PRECISION DEFAULT NULL, ADD max_res_op_exp_oper_n_maint DOUBLE PRECISION DEFAULT NULL');
    }
}
