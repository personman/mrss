<?php
/**
 * Test the peerGroup entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\PeerGroup;
use PHPUnit_Framework_TestCase;

/**
 * Class PeerGroupTest
 *
 * @package MrssTest\Model
 */
class PeerGroupTest extends PHPUnit_Framework_TestCase
{
    /** @var  PeerGroup */
    protected $peerGroup;

    public function setUp()
    {
        $this->peerGroup = new PeerGroup;
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\PeerGroup', $this->peerGroup);
    }

    public function testSetters()
    {
        $this->peerGroup->setId(5)
            ->setYear(2014)
            ->setName('Test Peergroup')
            ->setStates(array('MO', 'KS'))
            ->setEnvironments(array('Urban'))
            ->setWorkforceEnrollment('2000 - 4000')
            ->setWorkforceRevenue('1900000 - 5000000')
            ->setServiceAreaPopulation('10000 - 100000')
            ->setServiceAreaUnemployment('3 - 6')
            ->setServiceAreaMedianIncome('20000 - 80000');

        $this->assertEquals(5, $this->peerGroup->getId());
        $this->assertEquals(2014, $this->peerGroup->getYear());
        $this->assertEquals('Test Peergroup', $this->peerGroup->getName());
        $this->assertEquals(array('MO', 'KS'), $this->peerGroup->getStates());
        $this->assertEquals(array('Urban'), $this->peerGroup->getEnvironments());
        $this->assertEquals(
            '2000 - 4000',
            $this->peerGroup->getWorkforceEnrollment()
        );
        $this->assertEquals(
            '1900000 - 5000000',
            $this->peerGroup->getWorkforceRevenue()
        );
        $this->assertEquals(
            '10000 - 100000',
            $this->peerGroup->getServiceAreaPopulation()
        );
        $this->assertEquals(
            '3 - 6',
            $this->peerGroup->getServiceAreaUnemployment()
        );
        $this->assertEquals(
            '20000 - 80000',
            $this->peerGroup->getServiceAreaMedianIncome()
        );
    }

    public function testEmptyFilters()
    {
        $this->peerGroup->setStates(array());
        $this->assertEquals(array(), $this->peerGroup->getStates());

        $this->peerGroup->setEnvironments(array());
        $this->assertEquals(array(), $this->peerGroup->getEnvironments());
    }

    /**
     * @param $range
     * @param $expectedResult
     * @dataProvider getRanges
     */
    public function testParseRange($range, $expectedResult)
    {
        $results = $this->peerGroup->parseRange($range);

        $this->assertEquals($results, $expectedResult);
    }

    public function testHasCriteria()
    {
        $this->assertFalse($this->peerGroup->hasCriteria());

        $this->peerGroup->setStates(array('MO'));
        $this->assertTrue($this->peerGroup->hasCriteria());

        $this->peerGroup->setStates(array());
        $this->assertFalse($this->peerGroup->hasCriteria());

        $this->peerGroup->setServiceAreaUnemployment('3-6');
        $this->assertTrue($this->peerGroup->hasCriteria());
    }

    public function testGettersWithMinMax()
    {
        $this->peerGroup->setWorkforceEnrollment('100-500');
        $this->assertEquals(100, $this->peerGroup->getWorkforceEnrollment('min'));
        $this->assertEquals(500, $this->peerGroup->getWorkforceEnrollment('max'));

        $this->peerGroup->setWorkforceRevenue('100000-500000');
        $this->assertEquals(100000, $this->peerGroup->getWorkforceRevenue('min'));
        $this->assertEquals(500000, $this->peerGroup->getWorkforceRevenue('max'));

        $this->peerGroup->setServiceAreaPopulation('10000-50000');
        $this->assertEquals(10000, $this->peerGroup->getServiceAreaPopulation('min'));
        $this->assertEquals(50000, $this->peerGroup->getServiceAreaPopulation('max'));

        $this->peerGroup->setServiceAreaUnemployment('3-5');
        $this->assertEquals(3, $this->peerGroup->getServiceAreaUnemployment('min'));
        $this->assertEquals(5, $this->peerGroup->getServiceAreaUnemployment('max'));

        $this->peerGroup->setServiceAreaMedianIncome('30000-50000');
        $this->assertEquals(30000, $this->peerGroup->getServiceAreaMedianIncome('min'));
        $this->assertEquals(50000, $this->peerGroup->getServiceAreaMedianIncome('max'));
    }

    public function getRanges()
    {
        return array(
            array(
                '500 - 1000',
                array(
                    'min' => 500,
                    'max' => 1000
                )
            ),
            array(
                '3-10',
                array(
                    'min' => 3,
                    'max' => 10
                )
            )
        );
    }
}
