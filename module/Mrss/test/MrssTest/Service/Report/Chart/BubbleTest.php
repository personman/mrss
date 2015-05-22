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

    public function testSetFormat()
    {
        $format = '${y:,.2f}';
        $this->chart->setXFormat($format);
        $this->assertEquals($format, $this->chart->getXFormat());

        $format = '{y:,.1f}%';
        $this->chart->setYFormat($format);
        $this->assertEquals($format, $this->chart->getYFormat());
    }

    public function testConvertFormatForAxis()
    {
        $format = '${y:,.2f}';
        $this->assertEquals('${value:,.0f}', $this->chart->convertFormatForAxisLabel($format));
    }

    public function testUpdateFormats()
    {
        $xFormat = '${y:,.2f}';
        $yFormat = '{y:,.0f}%';

        $this->chart->setXFormat($xFormat);
        $this->chart->setYFormat($yFormat);

        $config = $this->chart->getConfig();

        $pointFormat = $config['plotOptions']['scatter']['tooltip']['pointFormat'];
        $this->assertContains('point.x', $pointFormat);
        $this->assertContains('point.y', $pointFormat);

        $this->assertEquals('${value:,.0f}', $config['xAxis']['labels']['format']);
        $this->assertEquals('{value:,.0f}%', $config['yAxis']['labels']['format']);
    }

    public function testSetLabel()
    {
        $xLabel = "This is the test X label";
        $yLabel = "And here's a Y label";

        $this->chart->setXLabel($xLabel);
        $this->chart->setYLabel($yLabel);

        $this->assertEquals($xLabel, $this->chart->getXLabel());
        $this->assertEquals($yLabel, $this->chart->getYLabel());

        $config = $this->chart->getConfig();
        $this->assertEquals($xLabel, $config['xAxis']['title']['text']);
        $this->assertEquals($yLabel, $config['yAxis']['title']['text']);
    }

    public function testUpdateLabels()
    {
        $xFormat = '${y:,.2f}';
        $yFormat = '{y:,.0f}%';
        $this->chart->setXFormat($xFormat);
        $this->chart->setYFormat($yFormat);

        $xLabel = "This is the test X label";
        $yLabel = "And here's a Y label";
        $this->chart->setXLabel($xLabel);
        $this->chart->setYLabel($yLabel);


        $config = $this->chart->getConfig();

        $pointFormat = $config['plotOptions']['scatter']['tooltip']['pointFormat'];
        $this->assertContains('point.x', $pointFormat);
        $this->assertContains('point.y', $pointFormat);
        $this->assertContains($xLabel, $pointFormat);
        $this->assertContains($yLabel, $pointFormat);
    }

    public function testAddMedianLines()
    {
        $xMedian = 8;
        $yMedian = 12;

        $this->chart->addMedianLines($xMedian, $yMedian);
        $config = $this->chart->getConfig();

        $this->assertEquals($xMedian, $config['xAxis']['plotLines'][0]['value']);
        $this->assertEquals($yMedian, $config['yAxis']['plotLines'][0]['value']);
    }
}
