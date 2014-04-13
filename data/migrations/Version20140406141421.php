<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140406141421 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_full_expend_ct DOUBLE PRECISION DEFAULT NULL, ADD inst_full_num_ct INT DEFAULT NULL, ADD inst_full_cred_hrs_ct INT DEFAULT NULL, ADD inst_part_expend_ct DOUBLE PRECISION DEFAULT NULL, ADD inst_part_num_ct INT DEFAULT NULL, ADD inst_part_cred_hrs_ct INT DEFAULT NULL, ADD inst_pt_perc_credit_hr_ct DOUBLE PRECISION DEFAULT NULL, ADD inst_full_expend_at DOUBLE PRECISION DEFAULT NULL, ADD inst_full_num_at INT DEFAULT NULL, ADD inst_full_cred_hrs_at INT DEFAULT NULL, ADD inst_ft_perc_credit_hr_at DOUBLE PRECISION DEFAULT NULL, ADD inst_part_expend_at DOUBLE PRECISION DEFAULT NULL, ADD inst_part_num_at INT DEFAULT NULL, ADD inst_part_cred_hrs_at INT DEFAULT NULL, ADD inst_admin_expend DOUBLE PRECISION DEFAULT NULL, ADD inst_admin_num INT DEFAULT NULL, ADD inst_o_cost DOUBLE PRECISION DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_full_expend_ct, DROP inst_full_num_ct, DROP inst_full_cred_hrs_ct, DROP inst_part_expend_ct, DROP inst_part_num_ct, DROP inst_part_cred_hrs_ct, DROP inst_pt_perc_credit_hr_ct, DROP inst_full_expend_at, DROP inst_full_num_at, DROP inst_full_cred_hrs_at, DROP inst_ft_perc_credit_hr_at, DROP inst_part_expend_at, DROP inst_part_num_at, DROP inst_part_cred_hrs_at, DROP inst_admin_expend, DROP inst_admin_num, DROP inst_o_cost");
    }
}
