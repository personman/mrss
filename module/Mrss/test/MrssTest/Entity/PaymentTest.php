<?php
/**
 * Test the payment entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Payment;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest
 *
 * @package MrssTest\Model
 */
class PaymentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Payment
     */
    protected $payment;

    public function setUp()
    {
        $this->payment = new Payment;
    }

    public function tearDown()
    {
        unset($this->study);
    }

    public function testinitialState()
    {
        $this->assertNull($this->payment->getId());
        $this->assertNull($this->payment->getTransId());
        $this->assertNull($this->payment->getCreated());
        $this->assertNull($this->payment->getProcessed());
        $this->assertNull($this->payment->getProcessedDate());
        $this->assertNull($this->payment->getPostback());
    }

    public function testSetters()
    {
        $this->payment->setId(5);
        $this->assertEquals(5, $this->payment->getId());

        $this->payment->setTransId(123);
        $this->assertEquals(123, $this->payment->getTransId());

        $this->payment->setPostback(array('test' => 'ok'));
        $postback = $this->payment->getPostback();
        $this->assertEquals('ok', $postback['test']);

        $this->payment->setProcessed(true);
        $this->assertTrue($this->payment->getProcessed());

        $now = new \DateTime('now');
        $this->payment->setCreated($now);
        $this->assertSame($now, $this->payment->getCreated());

        $now = new \DateTime('now');
        $this->payment->setProcessedDate($now);
        $this->assertSame($now, $this->payment->getProcessedDate());
    }
}
