<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151130165637 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations ADD ft_f_yminus3_degr_no_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_f_yminus4_degr_no_transf DOUBLE PRECISION DEFAULT NULL, ADD pt_f_yminus4_degr_no_transf DOUBLE PRECISION DEFAULT NULL, ADD ft_f_yminus7_degr_no_transf DOUBLE PRECISION DEFAULT NULL, ADD pt_f_yminus7_degr_no_transf DOUBLE PRECISION DEFAULT NULL');

        $this->addSql("delete from benchmarks where dbColumn IN(
'max_res_male_cred_stud',
'max_res_op_exp_inst',
'max_res_op_exp_student_services',
'max_res_op_exp_acad_supp',
'max_res_op_exp_inst_support',
'max_res_op_exp_research',
'max_res_op_exp_pub_serv',
'max_res_op_exp_oper_n_maint'
);
");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE observations DROP ft_f_yminus3_degr_no_transf, DROP ft_f_yminus4_degr_no_transf, DROP pt_f_yminus4_degr_no_transf, DROP ft_f_yminus7_degr_no_transf, DROP pt_f_yminus7_degr_no_transf');
    }
}
