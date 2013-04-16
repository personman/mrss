<?php

namespace MrssTest\Entity;

use Mrss\Entity\BenchmarkGroup;
use PHPUnit_Framework_TestCase;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Entity
 */
class BenchmarkGroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testUserInitialState()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $this->assertNull(
            $benchmarkGroup->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getName(),
            '"name" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getShortName(),
            '"shortName" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getDescription(),
            '"description" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getSequence(),
            '"sequence" should initially be null'
        );
    }

    public function testSetters()
    {
        $benchmarkGroup = new BenchmarkGroup;

        // Set Label
        $benchmarkGroup->setName('Financial Data');
        $this->assertEquals('Financial Data', $benchmarkGroup->getName());

        // Set shortName
        $benchmarkGroup->setShortName('form_1');
        $this->assertEquals('form_1', $benchmarkGroup->getShortName());

        // Set description
        $benchmarkGroup->setDescription('lorem ipsum');
        $this->assertEquals('lorem ipsum', $benchmarkGroup->getDescription());

        // Set sequence
        $benchmarkGroup->setSequence(2);
        $this->assertEquals(2, $benchmarkGroup->getSequence());
    }

    public function testBenchmarkAssociation()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $benchmarkGroup->getBenchmarks()
        );
    }

    public function testGetElements()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $benchmarkGroup->getElements()
        );
    }

    public function testGetLabel()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $benchmarkGroup->setName('Form 18');

        $this->assertEquals('Form 18', $benchmarkGroup->getLabel());
    }
}
