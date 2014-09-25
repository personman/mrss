<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140925084516 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $headings = array(
            2 => array(
                '% Completed in Three Years',
                '% Transferred in Three Years',
                '% Compl. or Transf. in Three Years',
                '% Completed in Six Years',
                '% Transferred in Six Years',
                '% Compl. or Transf. in Six Years'
            ),
            5 => array(
                'Noel-Levitz Summary Items',
                'Noel-Levitz Satisfaction Scales',
                'CCSSE Benchmarks',
                'ACT Student Opinion Survey'
            )
        );
        foreach ($headings as $id => $names) {
            $i = 40;
            foreach ($names as $name) {
                $this->addSql("INSERT INTO benchmark_headings (name, benchmarkGroup_id, sequence) VALUES ('$name', $id, $i)");
                $i++;
            }
        }


    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM benchmark_headings");
    }
}
