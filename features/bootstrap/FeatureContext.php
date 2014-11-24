<?php

use Behat\Behat\Context\BehatContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Exception\PendingException;
use Behat\Behat\Context\Step;
use Behat\Behat\Event\StepEvent;

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

        $dbConfig = $config['db']['adapters']['db'];
        preg_match('/dbname\=(.*)\;/', $dbConfig['dsn'], $matches);
        $dbName = $matches[1];
        $dbUser = $dbConfig['username'];
        $dbPassword = $dbConfig['password'];
        $fileToLoad = __DIR__ . '/' . self::$sqlPrepareFile;

        // Load from .sql file
        //$command = "mysql -u $dbUser -p$dbPassword $dbName < $fileToLoad";
        $command = null;
        // Don't actually run it yet. We do that before each scenario
        self::$sqlPrepareCommand = $command;


        // Or make queries directly using the ZF db adapter
        //$db = self::getZfDbAdapter();
        //$u = $db->query("delete from user", 'execute');
    }

    /**
     * Wrie Behat config file to suppress emails
     *
     * @BeforeSuite
     */
    public static function addConfigFile()
    {
        $source = 'features/bootstrap/behat.local.php';
        $destination = 'config/autoload/behat.local.php';

        copy($source, $destination);
    }

    /**
     * Remove the config file
     *
     * @AfterSuite
     */
    public static function removeConfigFile()
    {
        $destination = 'config/autoload/behat.local.php';

        unlink($destination);
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
        //shell_exec(self::$sqlPrepareCommand);
    }


    /**
     * @Given /^I am logged out$/
     */
    public function iAmLoggedOut()
    {
        $this->visit("/user/logout");
    }

    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->visit("/");

        // Are we already logged in?
        $logoutLink = $this->getSession()->getPage()
            ->find('xpath', "//a[contains(@href, 'logout')]");

        if (!empty($logoutLink)) {
            return true;
        }

        $email = 'dfergu15@jccc.edu';
        $password = '111111';

        $this->visit('/user/login');
        $this->fillField('identity', $email);
        $this->fillField('credential', $password);
        $this->pressButton('Sign In');
    }

    /**
     * @Then /^show the page$/
     */
    public function showThePage()
    {
        echo $this->getSession()->getPage()->getContent();
    }


    /**
     * @Given /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds($seconds)
    {
        //$this->getSession()->wait(1000*$seconds);
        sleep(intval($seconds));
    }

    /**
     * If the step fails, show the full page
     * @--AfterStep
     */
    /*public function afterStep(StepEvent $event)
    {
        $result = $event->getResult();
        if ($result == 4) {
            //$this->showThePage();

            //$this->dumpDb('mrss-failed-' . microtime(1) . '.sql');
        }
    }*/

    public function dumpDb($filename)
    {
        exec('mysqldump -u root -proot mrss_test > ' . $filename);
    }
}
