<?php

namespace MrssTest\Entity;

use Mrss\Entity\PercentileRank;
use PHPUnit_Framework_TestCase;
use ZendTest\Di\TestAsset\SetterInjection\B;

/**
 * Class PercentileRankTest
 *
 * @package MrssTest\Entity
 */
class PercentileRankTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PercentileRank
     */
    protected $percentileRank;

    public function setUp()
    {
        $this->percentileRank = new PercentileRank();
    }

    public function tearDown()
    {
        unset($this->percentileRank);
    }

    public function testSetters()
    {
        $this->percentileRank->setId(1);
        $this->assertEquals(1, $this->percentileRank->getId());

        $this->percentileRank->setYear(2014);
        $this->assertEquals(2014, $this->percentileRank->getYear());

        $this->percentileRank->setCipCode(12.0303);
        $this->assertEquals(12.0303, $this->percentileRank->getCipCode());

        $benchmarkMock = $this->getMock('Mrss\Entity\Benchmark');
        $this->percentileRank->setBenchmark($benchmarkMock);
        $this->assertEquals($benchmarkMock, $this->percentileRank->getBenchmark());

        $collegeMock = $this->getMock('Mrss\Entity\College');
        $this->percentileRank->setCollege($collegeMock);
        $this->assertEquals($collegeMock, $this->percentileRank->getCollege());

        $this->percentileRank->setRank(90);
        $this->assertEquals(90, $this->percentileRank->getRank());
    }
}
