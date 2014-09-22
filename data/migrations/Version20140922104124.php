<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140922104124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD form17b_dist_learn_grad_a VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_b VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_c VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_d VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_p VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_f VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_w VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_total VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_a_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_b_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_c_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_d_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_p_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_f_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_w_perc VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_withdrawal VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_completed VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_enr_succ VARCHAR(20) DEFAULT NULL, ADD form17b_dist_learn_grad_anb VARCHAR(20) DEFAULT NULL, DROP group_form17b_dist_learn_grad_a, DROP group_form17b_dist_learn_grad_b, DROP group_form17b_dist_learn_grad_c, DROP group_form17b_dist_learn_grad_d, DROP group_form17b_dist_learn_grad_p, DROP group_form17b_dist_learn_grad_f, DROP group_form17b_dist_learn_grad_w, DROP group_form17b_dist_learn_grad_total, DROP group_form17b_dist_learn_grad_a_perc, DROP group_form17b_dist_learn_grad_b_perc, DROP group_form17b_dist_learn_grad_c_perc, DROP group_form17b_dist_learn_grad_d_perc, DROP group_form17b_dist_learn_grad_p_perc, DROP group_form17b_dist_learn_grad_f_perc, DROP group_form17b_dist_learn_grad_w_perc, DROP group_form17b_dist_learn_grad_withdrawal, DROP group_form17b_dist_learn_grad_completed, DROP group_form17b_dist_learn_grad_enr_succ, DROP group_form17b_dist_learn_grad_anb");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD group_form17b_dist_learn_grad_a VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_b VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_c VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_d VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_p VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_f VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_w VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_total VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_a_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_b_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_c_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_d_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_p_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_f_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_w_perc VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_withdrawal VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_completed VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_enr_succ VARCHAR(20) DEFAULT NULL, ADD group_form17b_dist_learn_grad_anb VARCHAR(20) DEFAULT NULL, DROP form17b_dist_learn_grad_a, DROP form17b_dist_learn_grad_b, DROP form17b_dist_learn_grad_c, DROP form17b_dist_learn_grad_d, DROP form17b_dist_learn_grad_p, DROP form17b_dist_learn_grad_f, DROP form17b_dist_learn_grad_w, DROP form17b_dist_learn_grad_total, DROP form17b_dist_learn_grad_a_perc, DROP form17b_dist_learn_grad_b_perc, DROP form17b_dist_learn_grad_c_perc, DROP form17b_dist_learn_grad_d_perc, DROP form17b_dist_learn_grad_p_perc, DROP form17b_dist_learn_grad_f_perc, DROP form17b_dist_learn_grad_w_perc, DROP form17b_dist_learn_grad_withdrawal, DROP form17b_dist_learn_grad_completed, DROP form17b_dist_learn_grad_enr_succ, DROP form17b_dist_learn_grad_anb");
    }
}
