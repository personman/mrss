<?php

namespace MrssTest\Entity;

use Mrss\Entity\Percentile;
use PHPUnit_Framework_TestCase;
use ZendTest\Di\TestAsset\SetterInjection\B;

/**
 * Class PercentileTest
 *
 * @package MrssTest\Entity
 */
class PercentileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Percentile
     */
    protected $percentile;

    public function setUp()
    {
        $this->percentile = new Percentile();
    }

    public function tearDown()
    {
        unset($this->percentile);
    }

    public function testSetters()
    {
        $this->percentile->setId(1);
        $this->assertEquals(1, $this->percentile->getId());

        $this->percentile->setYear(2014);
        $this->assertEquals(2014, $this->percentile->getYear());

        $this->percentile->setCipCode(12.0303);
        $this->assertEquals(12.0303, $this->percentile->getCipCode());

        $benchmarkMock = $this->getMock('Mrss\Entity\Benchmark');
        $this->percentile->setBenchmark($benchmarkMock);
        $this->assertEquals($benchmarkMock, $this->percentile->getBenchmark());

        $this->percentile->setPercentile(90);
        $this->assertEquals(90, $this->percentile->getPercentile());

        $this->percentile->setValue(22.32);
        $this->assertEquals(22.32, $this->percentile->getValue());
    }
}
