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

    /*public function testGetPages()
    {
        $config = array(
            'navigation' => array(
                'admin' => array(
                    'dashboard' => array(
                        'label' => 'Dashboard',
                        'route' => 'admin'
                    ),
                            'studies' => array(
                        'label' => 'Studies',
                        'controller' => 'studies',
                        'route' => 'studies'
                    )
                )
            )
        );
        $serviceLocatorMock = $this
            ->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocatorMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($config));

        $factory = new AdminNavigationFactory();
        $pages = $factory->getPages($serviceLocatorMock);
    }*/
}
