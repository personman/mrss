<?php

use Phinx\Migration\AbstractMigration;

class CreateColleges extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('college');
        $table->addColumn('name', 'string', array('null' => false))
            ->addColumn('ipeds', 'string', array('null' => false))
            ->addColumn('city', 'string')
            ->addColumn('latitude', 'float')
            ->addColumn('longitude', 'float')
            ->create();
    }
}