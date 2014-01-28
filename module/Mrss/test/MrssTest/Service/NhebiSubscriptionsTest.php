<?php

namespace MrssTest\Service;

use PHPUnit_Framework_TestCase;

class NhebiSubscriptionsTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    public function setUp()
    {
        // Don't use namespaces, since the class needs to be run on older PHP
        $file = dirname(dirname(dirname(dirname(__FILE__)))) .
            '/src/Mrss/Service/NhebiSubscriptions.php';
        require_once($file);
        $this->service = new \NhebiSubscriptions();
    }

    public function testSetConfiguration()
    {
        $this->service->setConfiguration(array());

        $this->assertEquals(array(), $this->service->getConfiguration());
    }

    public function testSetCurrentStudyCode()
    {
        $this->service->setCurrentStudyCode('mrss');

        $this->assertEquals('mrss', $this->service->getCurrentStudyCode());
    }
}
