<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

define('REQUEST_MICROTIME', microtime(true));


ini_set('display_errors', true);
error_reporting(E_ALL);

// Setup autoloading
require 'init_autoloader.php';
require 'debug.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
