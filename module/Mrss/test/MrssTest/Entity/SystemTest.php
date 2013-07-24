<?php

namespace MrssTest\Entity;

use Mrss\Entity\System;
use PHPUnit_Framework_TestCase;

/**
 * Class SystemTest
 *
 * @package MrssTest\Entity
 */
class SystemTest extends PHPUnit_Framework_TestCase
{
    /** @var System */
    protected $system;

    public function setUp()
    {
        $this->system = new System();
    }

    public function testInitialState()
    {
        $this->assertNull($this->system->getId());
        $this->assertNull($this->system->getName());

        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $this->system->getColleges()
        );
    }

    /**
     * @dataProvider getSystemData
     * @param $systemData
     */
    public function testSetters($systemData)
    {
        $this->system->setId($systemData['id']);
        $this->system->setName($systemData['name']);
        $this->system->setIpeds($systemData['ipeds']);
        $this->system->setAddress($systemData['address']);
        $this->system->setAddress2($systemData['address2']);
        $this->system->setCity($systemData['city']);
        $this->system->setState($systemData['state']);
        $this->system->setZip($systemData['zip']);

        $this->assertEquals($systemData['id'], $this->system->getId());
        $this->assertEquals($systemData['name'], $this->system->getName());
        $this->assertEquals($systemData['ipeds'], $this->system->getIpeds());
        $this->assertEquals($systemData['address'], $this->system->getAddress());
        $this->assertEquals($systemData['address2'], $this->system->getAddress2());
        $this->assertEquals($systemData['city'], $this->system->getCity());
        $this->assertEquals($systemData['state'], $this->system->getState());
        $this->assertEquals($systemData['zip'], $this->system->getZip());
    }
    
    public function getSystemData()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'name' => 'Some College System',
                    'ipeds' => '999999',
                    'address' => '606 West Main Street',
                    'address2' => 'OCB 204',
                    'city' => 'Highland',
                    'state' => 'KS',
                    'zip' => 66035
                )
            )
        );

    }

    public function testAssociations()
    {
        $collegeMock = $this->getMock(
            'Mrss\Entity\College'
        );

        $this->system->setColleges(array($collegeMock));
        $colleges = $this->system->getColleges();

        $this->assertTrue(is_array($colleges));
        $this->assertInstanceOf(
            'Mrss\Entity\College',
            $colleges[0]
        );
    }

    public function testInputFilter()
    {
        $inputFilter = $this->system->getInputFilter();
        $this->assertInstanceOf('Zend\InputFilter\InputFilter', $inputFilter);
    }

    public function testGetAdmins()
    {
        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array('getRole')
        );
        $userMock->expects($this->once())
            ->method('getRole')
            ->will($this->returnValue('system_admin'));

        $collegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getUsers')
        );
        $collegeMock->expects($this->once())
            ->method('getUsers')
            ->will($this->returnValue(array($userMock)));

        $this->system->setColleges(array($collegeMock));
        $admins = $this->system->getAdmins();

        $this->assertInstanceOf(
            'Mrss\Entity\User',
            $admins[0]
        );
    }
}
