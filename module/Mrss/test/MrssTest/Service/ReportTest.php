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

        $key = $this->report->getSettingKey(2014);

        $this->assertEquals('report_calculated_3_2014', $key);
    }
}
