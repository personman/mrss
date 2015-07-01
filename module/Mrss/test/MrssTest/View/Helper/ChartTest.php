<?php

namespace MrssTest\View\Helper;

use Mrss\View\Helper\Chart;
use PHPUnit_Framework_TestCase;

class ChartTest extends PHPUnit_Framework_TestCase
{
    /** @var Chart  */
    protected $helper;

    public function setUp()
    {
        $this->helper = new Chart();

        // Mock dependencies
        $headScript = $this->getMock(
            'Zend\View\Helper\HeadScript',
            array('appendFile')
        );

        $view = $this->getMock(
            'Zend\View\Renderer\PhpRenderer',
            array('headScript')
        );
        $view->expects($this->any())
            ->method('headScript')
            ->will($this->returnValue($headScript));
        $this->helper->setView($view);
    }

    public function testInvoke()
    {
        // Invoke
        $helper = $this->helper;
        $this->assertContains('chartWrapper', $helper($this->getChartConfig()));
    }

    public function testInvokeWithNoConfig()
    {
        $helper = $this->helper;
        $this->assertInstanceOf(
            'Mrss\View\Helper\Chart',
            $helper()
        );
    }

    public function testGetChartJsUri()
    {
        $expected = '/js/highcharts.js?v=2';
        $this->assertEquals($expected, $this->helper->getChartJsUri());
    }

    protected function getChartConfig()
    {
        return array(
            'description' => 'lorem',
            'id' => 'test',
        );
    }
}
