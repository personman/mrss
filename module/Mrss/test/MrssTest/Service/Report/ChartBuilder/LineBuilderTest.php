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
}
