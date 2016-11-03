<?php

namespace MrssTest\Service\Report;

use Mrss\Service\Report\Calculator;
use PHPUnit_Framework_TestCase;

class CalculatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Calculator
     */
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

    public function testGetValueForPercentileBreakPointNOne()
    {
        $data = array(5);
        $this->calculator->setData($data);

        $value = $this->calculator->getValueForPercentile(50);

        $this->assertEquals(5, $value);
    }

    public function testGetValueForPercentileBreakPointJInteger()
    {
        $data = array(50, 35, 20, 40, 90, 12, 4);
        $this->calculator->setData($data);

        $value = $this->calculator->getValueForPercentile(88);

        $this->assertEquals(90, $value);
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

    /**
     * @param $data
     * @param $expectedOutliers
     * @dataProvider getOutlierData
     */
    public function testGetOutliers($data, $expectedOutliers)
    {
        $this->calculator->setData($data);
        $outliers = $this->calculator->getOutliers();

        $this->assertSame($expectedOutliers, $outliers);
    }

    public function testGetMedian()
    {
        $data = array(5, 10, 15);
        $this->calculator->setData($data);

        $this->assertEquals(10, $this->calculator->getMedian());
    }

    public function testGetMean()
    {
        $data = array(5, 10, 18);
        $this->calculator->setData($data);

        $this->assertEquals(11, $this->calculator->getMean());
    }

    public function testGetCount()
    {
        $this->assertEquals(0, $this->calculator->getCount());

        $data = array(5, 12, 13);
        $this->calculator->setData($data);
        $this->assertEquals(3, $this->calculator->getCount());
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider getStandardDeviationData
     */
    public function testGetStandardDeviation($data, $expected)
    {
        $this->calculator->setData($data);

        $stdDev = $this->calculator->getStandardDeviation();

        // Let's not be too picky
        $stdDev = round($stdDev, 5);
        $expected = round($expected, 5);

        $this->assertEquals($expected, $stdDev);
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

    public function getOutlierData()
    {
        return array(
            array(
                array(1 => 50, 2 => 51, 3 => 52, 4 => 52, 5 => 1000, 6 => 1, 7 => 1, 8 => 1),
                array(
                    array(
                        'college' => 5,
                        'value' => 1000,
                        'problem' => 'high'
                    )
                )
            ),
            array(
                array(1 => 1000, 2 => 1000, 3 => 1000, 4 => 1000, 5 => 1000, 6 => 1000, 7 => 1000, 8 => 2),
                array(
                    array(
                        'college' => 8,
                        'value' => 2,
                        'problem' => 'low'
                    )
                )
            ),
            // No outliers in this one:
            array(
                array(1 => 1000, 2 => 1000, 3 => 1000, 4 => 1000, 5 => 1000, 6 => 1000, 7 => 1, 8 => 2),
                array()
            )
        );
    }

    /**
     * Calculated these in Excel using STDEVP()
     *
     * @return array
     */
    public function getStandardDeviationData()
    {
        return array(
            array(
                array(10, 20, 1, 22, 13, 12, 55, 76),
                24.0802694
            ),
            array(
                array(34, 44, 75, 4, 8, 66, 33, 6),
                25.41038174
            )
        );
    }
}
