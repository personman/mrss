<?php

namespace MrssTest\Service;

use Mrss\Service\Report;
use PHPUnit_Framework_TestCase;

class ReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    protected $report;

    public function setUp()
    {
        $this->report = new Report();
    }

    public function testClassInstantiated()
    {
        $this->assertInstanceOf('Mrss\Service\Report', $this->report);
    }

    public function testGetSettingKey()
    {
        $studyMock = $this->getMock('\Mrss\Entity\Study');
        $studyMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(3));
        $this->report->setStudy($studyMock);

        $key = $this->report->getReportCalculatedSettingKey(2014);

        $this->assertEquals('report_calculated_3_2014', $key);
    }

    /**
     * @param $number
     * @param $expected
     * @dataProvider getOrdinalExamples
     */
    public function testGetOrdinal($number, $expected)
    {
        $this->assertEquals($expected, $this->report->getOrdinal($number));
    }

    public function getOrdinalExamples()
    {
        return array(
            array(2, '2<sup>nd</sup>'),
            array(3, '3<sup>rd</sup>'),
            array(0.1, '<1<sup>st</sup>'),
            array(99.99, '>99<sup>th</sup>'),
            array(11, '11<sup>th</sup>')
        );
    }

    public function testGetYourCollegeLabel()
    {
        $this->assertEquals('Your College', $this->report->getYourCollegeLabel());
    }

    public function testIsBenchmarkExclude()
    {
        $benchmarkMock = $this->getMock(
            '\Mrss\Entity\Benchmark',
            array('getDbColumn')
        );
        $benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will(
                $this->returnValue('institutional_demographics_campus_environment')
            );

        $this->assertTrue(
            $this->report->isBenchmarkExcludeFromReport($benchmarkMock)
        );
    }
}
