<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130718120110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations ADD inst_program_dev DOUBLE PRECISION DEFAULT NULL, ADD inst_course_planning DOUBLE PRECISION DEFAULT NULL, ADD inst_teaching DOUBLE PRECISION DEFAULT NULL, ADD inst_tutoring DOUBLE PRECISION DEFAULT NULL, ADD inst_advising DOUBLE PRECISION DEFAULT NULL, ADD inst_ac_service DOUBLE PRECISION DEFAULT NULL, ADD inst_assessment DOUBLE PRECISION DEFAULT NULL, ADD ss_admissions DOUBLE PRECISION DEFAULT NULL, ADD ss_advising DOUBLE PRECISION DEFAULT NULL, ADD ss_counceling DOUBLE PRECISION DEFAULT NULL, ADD ss_career DOUBLE PRECISION DEFAULT NULL, ADD ss_financial_aid DOUBLE PRECISION DEFAULT NULL, ADD ss_registrar DOUBLE PRECISION DEFAULT NULL, ADD ss_tutoring DOUBLE PRECISION DEFAULT NULL, ADD ss_testing DOUBLE PRECISION DEFAULT NULL, ADD ss_recruitment DOUBLE PRECISION DEFAULT NULL, ADD ss_cocurricular DOUBLE PRECISION DEFAULT NULL, ADD as_tech DOUBLE PRECISION DEFAULT NULL, ADD as_library DOUBLE PRECISION DEFAULT NULL, ADD as_experiential DOUBLE PRECISION DEFAULT NULL");
        $this->addSql("DROP INDEX users_email ON users");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE observations DROP inst_program_dev, DROP inst_course_planning, DROP inst_teaching, DROP inst_tutoring, DROP inst_advising, DROP inst_ac_service, DROP inst_assessment, DROP ss_admissions, DROP ss_advising, DROP ss_counceling, DROP ss_career, DROP ss_financial_aid, DROP ss_registrar, DROP ss_tutoring, DROP ss_testing, DROP ss_recruitment, DROP ss_cocurricular, DROP as_tech, DROP as_library, DROP as_experiential");
        $this->addSql("CREATE INDEX users_email ON users (email)");
    }
}
