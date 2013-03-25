<?php

require('db.php');

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                return new Zend\Db\Adapter\Adapter(array(
                    'driver'    => 'pdo',
                    'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
                    'database'  => $dbParams['database'],
                    'username'  => $dbParams['username'],
                    'password'  => $dbParams['password'],
                    'hostname'  => $dbParams['hostname'],
                ));
            },
        ),
    ),

    // Used by Phinx:
    'db' => array (
        'dsn' => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
        'username' => $dbParams['username'],
        'password' => $dbParams['password'],
        'port' => 3306,
    ),
);