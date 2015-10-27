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
            ->setPeers(array(1, 2, 3));

        $this->assertEquals(5, $this->peerGroup->getId());
        $this->assertEquals(2014, $this->peerGroup->getYear());
        $this->assertEquals('Test Peergroup', $this->peerGroup->getName());

        $this->assertEquals(
            array(1, 2, 3),
            $this->peerGroup->getPeers()
        );
    }

    public function testEmptyBenchmarksAndPeers()
    {
        $this->peerGroup->setBenchmarks(array())
            ->setPeers(array());

        $this->assertEquals(
            array(),
            $this->peerGroup->getBenchmarks()
        );

        $this->assertEquals(
            array(),
            $this->peerGroup->getPeers()
        );
    }

    public function testCollegeAssociation()
    {
        $collegeMock = $this->getMock('\Mrss\Entity\College');

        $this->peerGroup->setCollege($collegeMock);
        $this->assertInstanceOf(
            '\Mrss\Entity\College',
            $this->peerGroup->getCollege()
        );
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
