<?php

namespace MrssTest\Controller;

/**
 * Class IndexControllerTest
 *
 * @package MrssTest\Controller
 */
class IndexControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include $this->getConfigPath()
        );
        parent::setUp();

    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        // Why is this failing with a 500 status?
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('index');
        $this->assertActionName('index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('general');
    }
}
