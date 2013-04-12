<?php

namespace MrssTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class AbstractControllerTestCase
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class AbstractControllerTestCase extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockNavService();
    }

    protected function getConfigPath()
    {
        $appBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $configPath = $appBase . '/config/application.config.php';

        return $configPath;
    }

    protected function getServiceLocator()
    {
        $sm = $this->getApplicationServiceLocator();
        $sm->setAllowOverride(true);

        return $sm;
    }

    protected function mockNavService()
    {
        // Always mock the navigation service
    $nav = $this->getMock(
    'Zend\Navigation\AbstractContainer'
    );
    $this->getServiceLocator()->setService('navigation', $nav);

    }
}
