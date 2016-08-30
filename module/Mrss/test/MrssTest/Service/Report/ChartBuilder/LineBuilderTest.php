<?php

namespace MrssTest\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder\LineBuilder;
use PHPUnit_Framework_TestCase;

class LineBuilderTest extends PHPUnit_Framework_TestCase
{
    /** @var  LineBuilder */
    protected $lineBuilder;

    public function setUp()
    {
        $this->lineBuilder = new LineBuilder();
    }

    public function testSyncArrays()
    {
        $one = array(
            2015 => 10,
            2016 => 15
        );

        $two = array(
            2014 => 12,
            2016 => 18
        );

        list($syncedOne, $syncedTwo) = $this->lineBuilder->syncArrays($one, $two);

        $this->assertTrue(is_array($syncedOne));
        $this->assertTrue(count($syncedOne) == 3);
        $this->assertTrue(count($syncedTwo) == 3);
    }

    /**
     * @param $masterArray
     * @param $secondArray
     * @dataProvider getGapsData
     */
    public function testFillInGaps($masterArray, $secondArray, $masterExpected, $secondExpected)
    {
        list($masterResult, $secondResult) = $this->lineBuilder->fillInGaps($masterArray, $secondArray);

        $this->assertEquals($masterExpected, $masterResult);
        $this->assertEquals($secondExpected, $secondResult);
    }

    public function getGapsData()
    {
        $data = array(
            array(
                array(
                    2012 => 25,
                    2014 => 44,
                    2015 => 45
                ),
                array(
                    2011 => 5,
                    2012 => 8,
                    2015 => 9,
                    2016 => 11
                ),
                // Master result expected results
                array(
                    2012 => 25,
                    2013 => null,
                    2014 => 44,
                    2015 => 45
                ),
                // Second result expected results
                array(
                    2012 => 8,
                    2013 => null,
                    2014 => null,
                    2015 => 9
                ),

            )
        );

        return $data;
    }

    /**
     * @param $array
     * @param $expectedRange
     * @dataProvider getRangeData
     */
    public function testGetYearRange($array, $expectedRange)
    {
        $range = $this->lineBuilder->getYearRange($array);

        $this->assertEquals($expectedRange, $range);
    }

    public function getRangeData()
    {
        return array(
            array(
                array(
                    2012 => 5,
                    2015 => 3
                ),
                array(
                    2012,
                    2013,
                    2014,
                    2015
                )
            )
        );
    }
}
