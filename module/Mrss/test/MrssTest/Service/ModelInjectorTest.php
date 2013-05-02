<?php

namespace MrssTest\Service;

use Mrss\Service\ModelInjector;
use PHPUnit_Framework_TestCase;

/**
 * Class ModelInjectorTest
 *
 * @package MrssTest\Service
 */
class ModelInjectorTest extends PHPUnit_Framework_TestCase
{

    public function testPostLoad()
    {
        $serviceLocatorMock = $this->getMock(
            'Zend\Di\ServiceLocator',
            array('get'),
            array(),
            '',
            false
        );

        $entityMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('setBenchmarkModel')
        );

        $eventMock = $this->getMock(
            'Zend\EventManager\Event',
            array('getEntity')
        );
        $eventMock->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entityMock));

        $service = new ModelInjector($serviceLocatorMock);

        $service->postLoad($eventMock);
    }
}
