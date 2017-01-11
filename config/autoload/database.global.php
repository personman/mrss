<?php

require('db.php');

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
        ),
    ),
    'db' => array(
        'adapters' => array(
            'db' => array (
                'driver' => 'pdo',
                'dsn' => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
                'username' => $dbParams['username'],
                'password' => $dbParams['password'],
                'port' => 3306,
            ),
            'workforce-db' => array(
                'driver' => 'pdo',
            ),
        )
    )
);
