<?php

require('db.php');

return array(
    'doctrine' => array(

        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'dbname'   => $dbParams['database'],
                    'user'     => $dbParams['username'],
                    'password' => $dbParams['password'],
                )
            )
        ),
        'migrations_configuration' => array(
            'orm_default' => array(
                'directory' => 'data/DoctrineORMModule/Migrations',
                'namespace' => 'DoctrineMigrations',
                'table'     => 'migrations',
            ),
        ),
    )
);
