<?php
/**
 * Test the study entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Study;
use PHPUnit_Framework_TestCase;
use DateTime;

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
        $this->assertNull($this->study->getOutlierReportsOpen());
        $this->assertNull($this->study->getUPayUrl());
        $this->assertNull($this->study->getUPaySiteId());
        $this->assertNull($this->study->getLogo());
        $this->assertNull($this->study->getGoogleAnalyticsKey());
        $this->assertEquals(0, count($this->study->getOfferCodes()));

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

        $this->study->setOutlierReportsOpen(true);
        $this->assertTrue($this->study->getOutlierReportsOpen());

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

        $subscription = $this->getMock('\Mrss\Entity\Subscription', array());
        $this->study->setSubscriptions(array($subscription));
        $subs = $this->study->getSubscriptions();
        $this->assertSame($subscription, $subs[0]);
    }

    /*public function testCompletionPercentage()
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
    }*/

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
        $date = new DateTime('2011-08-04');
        $this->study->setEarlyPriceDate($date);

        /** @var DateTime $dateThisYear */
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

        // Test with an early bird deadline in the past
        $this->setEarlyBirdValidity(false);
        $this->assertEquals(1200, $this->study->getCurrentPrice());

        // Test while early bird is still open
        $this->setEarlyBirdValidity(true);
        $this->assertEquals(1100, $this->study->getCurrentPrice());
    }

    protected function setEarlyBirdValidity($valid = true)
    {
        $now = new \DateTime('now');

        $interval = new \DateInterval('P2D');
        $earlyBirdDate = clone $now;

        if ($valid) {
            // Two days in the future
            $earlyBirdDate->add($interval);
        } else {
            // Two days in the past
            $earlyBirdDate->sub($interval);
        }

        $this->study->setEarlyPriceDate($earlyBirdDate);
    }


    public function testGetCurrentYearMinus()
    {
        $this->study->setCurrentYear(2014);
        $this->assertEquals(2013, $this->study->getCurrentYearMinus(1));
    }

    public function testCheckOfferCode()
    {
        $offerCodeMock = $this->getOfferCodeMock('airforum');
        $offerCodeMock2 = $this->getOfferCodeMock('wdi2014');
        $this->study->setOfferCodes(array($offerCodeMock, $offerCodeMock2));

        $this->assertTrue($this->study->checkOfferCode("airforum"));
        $this->assertTrue($this->study->checkOfferCode("wdi2014"));
        $this->assertFalse($this->study->checkOfferCode("not real"));
    }

    public function testGetOfferCodesArrayWithEmpty()
    {
        $codes = $this->study->getOfferCodesArray();

        $this->assertEquals(0, count($codes));
    }

    public function testGetOfferCodePrice()
    {
        $code = $this->getOfferCodeMock('1234', 200);
        $this->study->setOfferCodes(array($code));

        $price = $this->study->getOfferCodePrice('1234');
        $this->assertEquals(200, $price);

    }

    protected function getOfferCodeMock($code, $price = null)
    {
        $offerCodeMock = $this->getMock(
            '\Mrss\Entity\OfferCode',
            array('getCode', 'getPrice')
        );

        $offerCodeMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        if ($price) {
            $offerCodeMock->expects($this->any())
                ->method('getPrice')
                ->will($this->returnValue($price));
        }

        return $offerCodeMock;
    }

    public function testGetOfferCode()
    {
        $offerCodeMock = $this->getOfferCodeMock('airforum');
        $offerCodeMock2 = $this->getOfferCodeMock('wdi2014');
        $this->study->setOfferCodes(array($offerCodeMock, $offerCodeMock2));

        $offerCode = $this->study->getOfferCode('airforum');
        $this->assertSame($offerCodeMock, $offerCode);
    }

    public function testGetOfferCodeEmpty()
    {
        $this->assertNull($this->study->getOfferCode('not_real'));
    }

    public function testGetOfferCodePriceEmpty()
    {
        $this->study->setEarlyPriceDate(new \Datetime('2014-01-01'));
        $this->study->setPrice(1000);

        $this->assertEquals(1000, $this->study->getOfferCodePrice('not_real'));
    }

    public function testGetBenchmarksForYear()
    {
        $benchmarkMock = $this->getMock(
            '\Mrss\Entity\Benchmark'
        );

        $benchmarkGroupMock = $this->getMock(
            '\Mrss\Entity\BenchmarkGroup',
            array('getBenchmarksForYear')
        );

        $benchmarkGroupMock->expects($this->once())
            ->method('getBenchmarksForYear')
            ->with(2013)
            ->will($this->returnValue(array($benchmarkMock)));

        $this->study->setBenchmarkGroups(array($benchmarkGroupMock));

        $benchmarks = $this->study->getBenchmarksForYear(2013);
        $this->assertEquals(1, count($benchmarks));
        $this->assertSame($benchmarkMock, $benchmarks[0]);
    }

    public function testGetAllBenchmarkKeys()
    {
        $benchmark = $this->getMock(
            '\Mrss\Entity\Benchmark',
            array('getDbColumn')
        );
        $benchmark->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('fake_db_col'));

        $benchmarkGroup = $this->getMock(
            '\Mrss\Entity\BenchmarkGroup',
            array('getBenchmarks')
        );
        $benchmarkGroup->expects($this->once())
            ->method('getBenchmarks')
            ->will($this->returnValue(array($benchmark)));

        $this->study->setBenchmarkGroups(array($benchmarkGroup));
        $keys = $this->study->getAllBenchmarkKeys();

        $this->assertEquals('fake_db_col', $keys[0]);
    }

    public function testSectionEarlyPricing()
    {
        $expected = 143;
        $baseEarlyPrice = 99;

        $identifier = 1;
        $sectionPrice = 44;
        $sectionComboPrice = 67;
        $this->study->setEarlyPrice($baseEarlyPrice);

        $sections = array($this->mockSection($identifier, $sectionPrice, $sectionComboPrice));
        $this->study->setSections($sections);
        $this->setEarlyBirdValidity();

        $selectedSections = array($identifier);
        $price = $this->study->getCurrentPrice(false, $selectedSections);

        $this->assertEquals($expected, $price);
    }

    /* @todo: implement
     *
     *
     **/
     public function testSectionEarlyComboPricing()
    {
        $expected = 1950;

        $baseEarlyPrice = 0;
        $this->study->setEarlyPrice($baseEarlyPrice);

        $selectedSections = array();

        // Section 1
        $identifier = 1;
        $sectionPrice = 1450;
        $sectionComboPrice = 1450;
        $section1 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        // Section 2
        $identifier = 2;
        $sectionPrice = 950;
        $sectionComboPrice = 500;
        $section2 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        $sections = array($section1, $section2);
        $this->study->setSections($sections);
        $this->setEarlyBirdValidity();


        $price = $this->study->getCurrentPrice(false, $selectedSections);

        $this->assertEquals($expected, $price);
    }

    public function testSectionRegularComboPricing()
    {
        $expected = 500;

        $basePrice = 200;
        $this->study->setPrice($basePrice);

        $selectedSections = array();

        // Section 1
        $identifier = 1;
        $sectionPrice = 44;
        $sectionComboPrice = 50;
        $section1 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        // Section 2
        $identifier = 2;
        $sectionPrice = 4;
        $sectionComboPrice = 250;
        $section2 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        $sections = array($section1, $section2);
        $this->study->setSections($sections);
        $this->setEarlyBirdValidity(false);


        $price = $this->study->getCurrentPrice(false, $selectedSections);

        $this->assertEquals($expected, $price);
    }

    public function testSectionRenewalRegularComboPricing()
    {
        $expected = 500;

        $basePrice = 200;
        $this->study->setRenewalPrice($basePrice);

        $selectedSections = array();

        // Section 1
        $identifier = 1;
        $sectionPrice = 44;
        $sectionComboPrice = 50;
        $section1 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        // Section 2
        $identifier = 2;
        $sectionPrice = 4;
        $sectionComboPrice = 250;
        $section2 = $this->mockSection($identifier, $sectionPrice, $sectionComboPrice);
        $selectedSections[] = $identifier;

        $sections = array($section1, $section2);
        $this->study->setSections($sections);
        $this->setEarlyBirdValidity(false);


        $price = $this->study->getCurrentPrice(true, $selectedSections);

        $this->assertEquals($expected, $price);
    }

    public function testSetSections()
    {
        $sectionId = 1;
        $sectionPrice = 44;
        $sectionComboPrice = 67;

        $sections = array($this->mockSection($sectionId, $sectionPrice, $sectionComboPrice));
        $this->study->setSections($sections);

        $section = $this->study->getSection($sectionId);

        $this->assertEquals($sectionId, $section->getId());
        $this->assertEquals($sectionPrice, $section->getPrice());
    }

    protected function mockSection($sectionId, $price, $comboPrice)
    {
        $section = $this->getMock(
            '\Mrss\Entity\Section',
            array('getId', 'getPrice', 'getComboPrice')
        );
        $section->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($sectionId));
        $section->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));
        $section->expects($this->any())
            ->method('getComboPrice')
            ->will($this->returnValue($comboPrice));

        return $section;
    }
}
