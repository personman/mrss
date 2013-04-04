<?php

namespace MrssTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class ImportControllerTest
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class ImportControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $configPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))
        ) .
            '/config/application.config.php';
        $this->setApplicationConfig(
            include $configPath
        );
        parent::setUp();
    }

    /**
     * This isn't actually working. It doesn't execute the entire indexAction()
     */
    public function testIndexActionCanBeAccessed()
    {
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importColleges'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importColleges');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        $this->dispatch('/import');

        // It should redirect:
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('index');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    protected function getServiceLocator()
    {
        $sm = $this->getApplicationServiceLocator();
        $sm->setAllowOverride(true);

        return $sm;
    }
}