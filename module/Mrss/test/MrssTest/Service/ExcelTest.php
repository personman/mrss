<?php

namespace MrssTest\Service;

use Mrss\Service\Excel;
use PHPUnit_Framework_TestCase;

class ExcelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Excel
     */
    protected $service;

    public function setUp()
    {
        $this->service = new Excel();
    }

    public function testClassInstantiated()
    {
        $this->assertInstanceOf('Mrss\Service\Excel', $this->service);
    }

    /**
     * @param $number
     * @param $expected
     * @dataProvider getIpedsExamples
     */
    public function testExtractIpeds($number, $expected)
    {
        $this->assertEquals($expected, $this->service->extractIpeds($number));
    }

    public function getIpedsExamples()
    {
        return array(
            array('111111', '111111'),
            array('asdf', null)
        );
    }

    /**
     * @param $number
     * @param $expected
     * @dataProvider getNum2AlphaExamples
     */
    public function testNum2Alpha($number, $expected)
    {
        $this->assertEquals($expected, $this->service->num2alpha($number));
    }

    public function getNum2AlphaExamples()
    {
        return array(
            array(0, 'A'),
            array('1', 'B'),
            array('2', 'C'),
            array(3, 'D'),
            array(30, 'AE'),
            array(930, 'AIU')
        );
    }

    public function testSetDependencies()
    {
        $currentStudyMock = $this->getMock('\Mrss\Entity\Study');
        $this->service->setCurrentStudy($currentStudyMock);
        $this->assertSame($currentStudyMock, $this->service->getCurrentStudy());

        $currentCollegeMock = $this->getMock('\Mrss\Entity\Study');
        $this->service->setCurrentCollege($currentCollegeMock);
        $this->assertSame($currentCollegeMock, $this->service->getCurrentCollege());
    }
}
