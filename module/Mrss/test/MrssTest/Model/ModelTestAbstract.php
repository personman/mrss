<?php

namespace MrssTest\Model;

use PHPUnit_Framework_TestCase;

class ModelTestAbstract extends PHPUnit_Framework_TestCase
{
    protected function getEmMock($extraMethods = array())
    {
        $repositoryMock = $this->getMock(
            'Doctrine\Orm\Repository',
            array('findOneBy')
        );

        $methodsToMock = array(
            'getRepository',
            'getClassMetadata',
            'persist',
            'flush'
        );
        $methodsToMock = array_merge($methodsToMock, $extraMethods);

        $emMock  = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            $methodsToMock,
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }
}
