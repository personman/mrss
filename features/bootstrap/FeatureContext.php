<?php

use Behat\Behat\Context\BehatContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\MinkExtension\Context\MinkContext;
use Zend;

class FeatureContext extends MinkContext
{

    /** @var \Zend\Mvc\Application */
    private static $zendApp;

    /** @var string */
    private static $sqlPrepareFile = 'behat-prepare-test-db.sql';
    private static $sqlPrepareCommand;

    /**
     * Load ZF app so we can manipulate the db
     * https://speakerdeck.com/weaverryan/behavioral-driven-development-with-behat-and-zend-framework-2
     * @BeforeSuite */
    static public function initializeZendFramework()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // @todo: Move this to a lazy-loading method of its own
        if (self::$zendApp == null) {
            $path = __DIR__ . '/../../config/application.config.php';

            self::$zendApp = \Zend\Mvc\Application::init(
                require $path
            );
        }

        // Get the db config from ZF
        $config = self::$zendApp->getConfig();

        if (empty($config['db'])) {
            echo '$config: ';
            print_r($config);
        }

        $dbConfig = $config['db'];
        preg_match('/dbname\=(.*)\;/', $dbConfig['dsn'], $matches);
        $dbName = $matches[1];
        $dbUser = $dbConfig['username'];
        $dbPassword = $dbConfig['password'];
        $fileToLoad = __DIR__ . '/' . self::$sqlPrepareFile;

        // Load from .sql file
        $command = "mysql -u $dbUser -p$dbPassword $dbName < $fileToLoad";

        // Don't actually run it yet. We do that before each scenario
        self::$sqlPrepareCommand = $command;


        // Or make queries directly using the ZF db adapter
        //$db = self::getZfDbAdapter();
        //$u = $db->query("delete from user", 'execute');
    }

    protected static function getZfDbAdapter()
    {
        return self::$zendApp->getServiceManager()->get('Zend\Db\Adapter\Adapter');
    }



    /**
     * Alternatively, we could have a step(s) that triggers this so we don't run it unnecessarily
     *
     * @BeforeScenario */
    public static function testDbSetup()
    {
        shell_exec(self::$sqlPrepareCommand);
    }


    /**
     * @Given /^I am logged out$/
     */
    public function iAmLoggedOut()
    {
        $this->getSession()->visit("/user/logout");
    }

}