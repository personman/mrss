<?php
namespace ApplicationTest;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

class Bootstrap
{
    protected static $serviceManager;
    protected static $config;
    protected static $bootstrap;

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        chdir(__DIR__);

        // Show errors:
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Load the user-defined test configuration file, if it exists
        if (is_readable(__DIR__ . '/TestConfig.php')) {
            $testConfig = include __DIR__ . '/TestConfig.php';
        } else {
            $testConfig = include __DIR__ . '/TestConfig.php.dist';
        }

        $zfModulePaths = array();

        if (isset($testConfig['module_listener_options']['module_paths'])) {
            $modulePaths =
                $testConfig['module_listener_options']['module_paths'];
            foreach ($modulePaths as $modulePath) {
                if (($path = static::findParentPath($modulePath))) {
                    $zfModulePaths[] = $path;
                }
            }
        }

        $zfModulePaths  =
            implode(PATH_SEPARATOR, $zfModulePaths) . PATH_SEPARATOR;
        $zfModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ?:
            (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $baseConfig = array(
            'module_listener_options' => array(
                'module_paths' => explode(PATH_SEPARATOR, $zfModulePaths),
            ),
        );

        $config = ArrayUtils::merge($baseConfig, $testConfig);

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
        static::$config = $config;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getConfig()
    {
        return static::$config;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            $zfPath = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH :
                (is_dir($vendorPath . '/ZF2/library') ?
                    $vendorPath . '/ZF2/library' : false));

            if (!$zfPath) {
                throw new RuntimeException(
                    'Unable to load ZF2. Run `php composer.phar install`
                    or define a ZF2_PATH environment variable.'
                );
            }

            include $zfPath . '/Zend/Loader/AutoloaderFactory.php';

        }

        AutoloaderFactory::factory(
            array(
                'Zend\Loader\StandardAutoloader' => array(
                    'autoregister_zf' => true,
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                    ),
                ),
            )
        );
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }

            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();
