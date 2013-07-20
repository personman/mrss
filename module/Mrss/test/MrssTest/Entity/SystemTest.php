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

    public function testSetters()
    {
        $this->system->setId(5);
        $this->system->setName('test');

        $this->assertEquals(5, $this->system->getId());
        $this->assertEquals('test', $this->system->getName());
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
}
