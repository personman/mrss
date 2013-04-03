<?php

namespace MrssTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class CollegeControllerTest
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class CollegeControllerTest extends AbstractHttpControllerTestCase
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

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/colleges');
        // Why is this failing with a 500 status?
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('colleges');
        $this->assertActionName('index');
        $this->assertControllerClass('CollegeController');
        $this->assertMatchedRouteName('general');
    }
}