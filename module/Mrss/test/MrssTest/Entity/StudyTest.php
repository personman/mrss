<?php
/**
 * Test the study entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Study;
use PHPUnit_Framework_TestCase;

/**
 * Class StudyTest
 *
 * @package MrssTest\Model
 */
class StudyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Study
     */
    protected $study;

    public function setUp()
    {
        $this->study = new Study;
    }

    public function tearDown()
    {
        unset($this->study);
    }

    public function testinitialState()
    {
        $this->assertNull($this->study->getId());
        $this->assertNull($this->study->getName());
        $this->assertNull($this->study->getDescription());
        $this->assertNull($this->study->getCurrentYear());
        $this->assertNull($this->study->getPrice());
        $this->assertNull($this->study->getEarlyPrice());
        $this->assertNull($this->study->getEarlyPriceDate());
        $this->assertNull($this->study->getPilotOpen());
        $this->assertNull($this->study->getEnrollmentOpen());
        $this->assertNull($this->study->getDataEntryOpen());
        $this->assertNull($this->study->getReportsOpen());
        $this->assertNull($this->study->getUPayUrl());
        $this->assertNull($this->study->getUPaySiteId());
        $this->assertNull($this->study->getLogo());
        $this->assertNull($this->study->getGoogleAnalyticsKey());
        $this->assertNull($this->study->getOfferCodes());

        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $this->study->getBenchmarkGroups()
        );
    }

    public function testSetters()
    {
        $this->study->setName('NCCBP');
        $this->assertEquals('NCCBP', $this->study->getName());

        $this->study->setDescription('lorem');
        $this->assertEquals('lorem', $this->study->getDescription());

        $this->study->setCurrentYear(2013);
        $this->assertEquals(2013, $this->study->getCurrentYear());

        $groupsMock = array('placeholder');
        $this->study->setBenchmarkGroups($groupsMock);
        $this->assertEquals($groupsMock, $this->study->getBenchmarkGroups());

        $this->study->setPrice(1400);
        $this->assertEquals(1400, $this->study->getPrice());

        $this->study->setEarlyPrice(1200);
        $this->assertEquals(1200, $this->study->getEarlyPrice());

        $this->study->setEarlyPriceDate('2013-07-01');
        $this->assertEquals('2013-07-01', $this->study->getEarlyPriceDate());

        $this->study->setPilotOpen(true);
        $this->assertTrue($this->study->getPilotOpen());

        $this->study->setEnrollmentOpen(true);
        $this->assertTrue($this->study->getEnrollmentOpen());

        $this->study->setDataEntryOpen(true);
        $this->assertTrue($this->study->getDataEntryOpen());

        $this->study->setReportsOpen(true);
        $this->assertTrue($this->study->getReportsOpen());

        $this->study->setUPayUrl('http://test.com');
        $this->assertEquals('http://test.com', $this->study->getUPayUrl());

        $this->study->setUPaySiteId(3);
        $this->assertEquals(3, $this->study->getUPaySiteId());

        $this->study->setLogo('/test.png');
        $this->assertEquals('/test.png', $this->study->getLogo());

        $this->study->setGoogleAnalyticsKey('123');
        $this->assertEquals('123', $this->study->getGoogleAnalyticsKey());

        $this->study->setOfferCodes(array('test'));
        $this->assertEquals(array('test'), $this->study->getOfferCodes());
        $this->assertTrue($this->study->hasOfferCode());
    }

    public function testCompletionPercentage()
    {
        $observationMock = $this->getMock('Mrss\Entity\Observation');

        // Test it with no benchmarkGroups
        $percentage = $this->study->getCompletionPercentage($observationMock);
        $this->assertEquals(0, $percentage);

        // Benchmark
        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('isAvailableForYear')
        );
        $benchmarkMock->expects($this->once())
            ->method('isAvailableForYear')
            ->will($this->returnValue(true));

        // Now test it with benchmarkGroups
        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getBenchmarks', 'countCompleteFieldsInObservation')
        );
        $benchmarkGroupMock->expects($this->once())
            ->method('getBenchmarks')
            ->will($this->returnValue(array($benchmarkMock)));

        $this->study->setBenchmarkGroups(array($benchmarkGroupMock));

        $percentage = $this->study->getCompletionPercentage($observationMock);
        $this->assertEquals(0, $percentage);
    }

    public function testGetInputFilter()
    {
        $filterMock = $this->getMock(
            'Zend\InputFilter\InputFilter'
        );

        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getFormElementInputFilter')
        );
        $benchmarkMock->expects($this->once())
            ->method('getFormElementInputFilter')
            ->will($this->returnValue($filterMock));

        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getNonComputedBenchmarksForYear')
        );
        $benchmarkGroupMock->expects($this->once())
            ->method('getNonComputedBenchmarksForYear')
            ->will($this->returnValue(array($benchmarkMock)));

        $this->study->setBenchmarkGroups(array($benchmarkGroupMock));
        $filter = $this->study->getInputFilter();
    }

    public function testGetEarlyPriceThisYear()
    {
        $date = new \DateTime('2011-08-04');
        $this->study->setEarlyPriceDate($date);

        $dateThisYear = $this->study->getEarlyPriceDateThisYear();
        $this->assertEquals($dateThisYear->format('Y'), date('Y'));
    }

    public function testGetEarlyPriceDate()
    {
        $date = new \DateTime('2013-08-04');
        $this->study->setEarlyPriceDate($date);
        $this->study->setCurrentYear(2014);

        $this->assertEquals(
            2014,
            $this->study->getEarlyPriceDateForStudyYear()->format('Y')
        );
    }

    public function testGetCurrentPrice()
    {
        $this->study->setPrice(1200);
        $this->study->setEarlyPrice(1100);
        $now = new \DateTime('now');

        // Two days in the past
        $interval = new \DateInterval('P2D');
        $earlyPricePast = clone $now;
        $earlyPricePast->sub($interval);
        $earlyPriceFuture = clone $now;
        $earlyPriceFuture->add($interval);

        // Test with an early bird deadline in the past
        $this->study->setEarlyPriceDate($earlyPricePast);
        $this->assertEquals(1200, $this->study->getCurrentPrice());

        // Test while early bird is still open
        $this->study->setEarlyPriceDate($earlyPriceFuture);
        $this->assertEquals(1100, $this->study->getCurrentPrice());
    }

    public function testGetCurrentYearMinus()
    {
        $this->study->setCurrentYear(2014);
        $this->assertEquals(2013, $this->study->getCurrentYearMinus(1));
    }

    /*public function testCheckOfferCode()
    {
        $this->study->setOfferCodes("wdi2014, abc2015");

        $this->assertTrue($this->study->checkOfferCode("ABC2015"));
        $this->assertTrue($this->study->checkOfferCode("wdi2014"));
        $this->assertFalse($this->study->checkOfferCode("not real"));
    }*/
}
