<?php

use Phinx\Migration\AbstractMigration;

class CreateUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('user', array('id' => 'user_id'));
        $table->addColumn('username', 'string', array('null' => true))
              ->addColumn('email', 'string', array('null' => true))
              ->addColumn('display_name', 'string', array('limit' => 50, 'null' => true))
              ->addColumn('password', 'string', array('limit' => 128))
              ->addColumn('state', 'integer', array('null' => true))
              ->create();
    }

}