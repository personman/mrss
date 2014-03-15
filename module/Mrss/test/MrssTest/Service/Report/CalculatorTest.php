<?php

namespace MrssTest\Service\Report;

use Mrss\Service\Report\Calculator;
use PHPUnit_Framework_TestCase;

class CalculatorTest extends PHPUnit_Framework_TestCase
{
    protected $calculator;

    public function setUp()
    {
        $this->calculator = new Calculator();
    }

    public function testCalculatorInstantiated()
    {
        $this->assertInstanceOf('Mrss\Service\Report\Calculator', $this->calculator);
    }

    public function testSetData()
    {
        $data = array(55, 33, 99);
        $this->calculator->setData($data);

        $this->assertSame($data, $this->calculator->getData());
    }

    /**
     * @param $data
     * @param $percentile
     * @param $expected
     * @internal param $dataAndResults
     * @dataProvider getBreakpointDataAndResults
     */
    public function testGetValueForPercentileBreakpoint($data, $percentile, $expected)
    {
        $this->calculator->setData($data);
        $value = $this->calculator->getValueForPercentile($percentile);

        $this->assertEquals($expected, $value);
    }

    public function getBreakpointDataAndResults()
    {
        return array(
            array(
                array(5, 34, 95, 299),
                25,
                12.25
            ),
            array(
                array(0, 0, 0, 100),
                50,
                0.0
            ),
            array(
                array(),
                10,
                null
            ),
            array(
                array(1, 2, 3, 4, 5),
                50,
                3
            ),
            array(
                array(1, 2, 3, 4),
                50,
                2.5
            ),
            array(
                array(100, 223, 553, 1, 9, 34),
                10,
                1.0
            )
        );
    }

    /**
     * @param $data
     * @param $value
     * @param $expected
     * @dataProvider getPercentileData
     */
    public function testGetPercentileForValue($data, $value, $expected)
    {
        $this->calculator->setData($data);
        $percentile = $this->calculator->getPercentileForValue($value);

        $this->assertEquals($expected, $percentile);
    }

    public function getPercentileData()
    {
        return array(
            array(
                array(5, 34, 95, 299),
                34,
                25
            ),
            array(
                array(5, 34, 95, 299),
                95,
                50.0
            ),
            array(
                array(123, 244, 12, 19, 23, 11, 33, 66, 11, 99, 99, 45),
                23,
                33.3333333333
            ),
            array(
                array(123, 244, 12, 19, 23, 11, 33, 66, 11, 99, 99, 45, 9),
                99,
                76.9230769231
            )
        );
    }
}
