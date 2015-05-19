<?php

namespace MrssTest\Service\Report\Chart;

use Mrss\Service\Report\Chart\Bubble;
use PHPUnit_Framework_TestCase;

class BubbleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Bubble
     */
    protected $chart;

    public function setUp()
    {
        $this->chart = new Bubble();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('Mrss\Service\Report\Chart\Bubble', $this->chart);
        $this->assertInstanceOf('Mrss\Service\Report\Chart\AbstractChart', $this->chart);
    }

    /**
     * Tests for abstract class
     */
    public function testToJson()
    {
        $this->assertNotEmpty($this->chart->toJson());
    }

    public function testGetConfig()
    {
        $config = array('one' => 'two');

        $this->chart->setConfig($config);
        $result = $this->chart->getConfig();

        $this->assertEquals('two', $result['one']);
    }

    public function testGetId()
    {
        $id = $this->chart->getId();

        $this->assertNotEmpty($id);
    }

    public function testSetTitle()
    {
        $this->chart->setTitle('Chart the first');

        $config = $this->chart->getConfig();

        $this->assertEquals('Chart the first', $config['title']['text']);
    }

    public function testSetSeries()
    {
        $this->chart->setSeries(array(array(1, 2, 3, 5, 7)));

        $config = $this->chart->getConfig();

        $this->assertEquals(1, $config['series'][0][0]);
    }
}
