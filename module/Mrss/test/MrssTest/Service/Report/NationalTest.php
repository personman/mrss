<?php

namespace MrssTest\Service\Report;

use Mrss\Service\Report\National;
use PHPUnit_Framework_TestCase;

class NationalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var National
     */
    protected $report;

    public function setUp()
    {
        $studyMock = $this->getMock('Mrss\Entity\Study');
        $this->report = new National($studyMock);
    }

    public function testSetup()
    {
        $this->assertInstanceOf('\Mrss\Service\Report\National', $this->report);
    }
}
