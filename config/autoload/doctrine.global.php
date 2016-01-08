<?php

require('db.php');

return array(
    'doctrine' => array(

        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => $dbParams['hostname'],
                    //'unix_socket' => '/Applications/MAMP/tmp/mysql/mysql.sock',
                    'port'     => '3306',
                    'dbname'   => $dbParams['database'],
                    'user'     => $dbParams['username'],
                    'password' => $dbParams['password'],
                ),
                /*'doctrine.cache.my_memcache' => function ($sm) {
                        $cache = new \Doctrine\Common\Cache\MemcacheCache();
                        $memcache = new \Memcache();
                        $memcache->connect('localhost', 11211);
                        $cache->setMemcache($memcache);
                        return $cache;
                    }*/
            )
        ),
        // migrations configuration
        'migrations_configuration' => array(
            'orm_default' => array(
                'directory' => 'data/migrations',
                'name'      => 'Doctrine Database Migrations',
                'namespace' => 'DoctrineMigrations',
                'table'     => 'doctrine_migration_versions',
            ),
        ),
        'configuration' => array(
            'orm_default' => array(
                // Caching Doctrine annotations shaves ~400ms off load times
                'metadata_cache' => 'filesystem',
                'query_cache' => 'filesystem',
            )
        ),
    )
);
