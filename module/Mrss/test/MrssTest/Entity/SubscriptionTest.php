<?php
/**
 * Test the subscription entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Subscription;
use PHPUnit_Framework_TestCase;

/**
 * Class SubscriptionTest
 *
 * @package MrssTest\Model
 */
class SubscriptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testInitialState()
    {
        $role = new Subscription();

        $this->assertNull(
            $role->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $role->getStatus(),
            '"status" should initially be null'
        );

        $this->assertNull(
            $role->getYear(),
            '"year" should initially be null'
        );

        $this->assertNull(
            $role->getCollege(),
            '"college" should initially be null'
        );

        $this->assertNull(
            $role->getStudy(),
            '"study" should initially be null'
        );

        $this->assertNull(
            $role->getPaymentMethod(),
            '"paymentMethod" should initially be null'
        );

        $this->assertNull(
            $role->getObservation(),
            '"observation" should initially be null'
        );
    }

    /**
     * Can we set subscription properties and do they stick?
     */
    public function testSetters()
    {
        $subscription = new Subscription();

        $subscription->setStatus('complete');
        $this->assertEquals('complete', $subscription->getStatus());

        $subscription->setYear(2013);
        $this->assertEquals(2013, $subscription->getYear());

        $subscription->setPaymentMethod('invoice');
        $this->assertEquals('invoice', $subscription->getPaymentMethod());

        $collegeMock = $this->getMock('Mrss\Entity\College');
        $subscription->setCollege($collegeMock);
        $this->assertSame($collegeMock, $subscription->getCollege());

        $studyMock = $this->getMock('Mrss\Entity\Study');
        $subscription->setStudy($studyMock);
        $this->assertSame($studyMock, $subscription->getStudy());

        $obMock = $this->getMock('Mrss\Entity\Observation');
        $subscription->setObservation($obMock);
        $this->assertSame($obMock, $subscription->getObservation());
    }
}
