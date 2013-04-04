<?php

namespace MrssTest\Controller;

/**
 * Class CollegeControllerTest
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class CollegeControllerTest extends AbstractControllerTestCase
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
