<?php

namespace Aaup;

use Zend\Mvc\MvcEvent;

class Module
{
    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';

        return $config;
    }

    /*public function getAutoloaderConfig()
    {
        return array(
            // This doesn't seem to help performance, oddly
            'Zend\Loader\ClassMapAutoloader' => array(
                dirname(dirname(__DIR__)) . '/autoload_classmap.php',
            ),
        );
    }*/

    /**
     * Remember to run php bin/classmap_generator.php after adding a new class
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array();
    }
}
