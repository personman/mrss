<?php

namespace MrssTest\Service;

use Mrss\Service\AdminNavigationFactory;
use PHPUnit_Framework_TestCase;

class AdminNavigationFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $factory = new AdminNavigationFactory;

        $this->assertEquals('admin', $factory->getName());
    }
}
