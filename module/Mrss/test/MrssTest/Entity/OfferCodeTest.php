<?php
/**
 * Test the peerGroup entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\OfferCode;
use PHPUnit_Framework_TestCase;

/**
 * Class OfferCodeTest
 *
 * @package MrssTest\Model
 */
class OfferCodeTest extends PHPUnit_Framework_TestCase
{
    /** @var  OfferCode */
    protected $offerCode;

    public function setUp()
    {
        $this->offerCode = new OfferCode;
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\OfferCode', $this->offerCode);
    }

    public function testSetters()
    {
        $this->offerCode->setId(5);
        $this->assertEquals(5, $this->offerCode->getId());

        $this->offerCode->setCode('TEST CODE');
        $this->assertEquals('TEST CODE', $this->offerCode->getCode());

        $this->offerCode->setPrice(99);
        $this->assertEquals(99, $this->offerCode->getPrice());

        $this->offerCode->setSkipOtherDiscounts(true);
        $this->assertEquals(true, $this->offerCode->getSkipOtherDiscounts());

        // Study mock
        $study = $this->getMock('\Mrss\Entity\Study');
        $this->offerCode->setStudy($study);
        $this->assertSame($study, $this->offerCode->getStudy());
    }
}
