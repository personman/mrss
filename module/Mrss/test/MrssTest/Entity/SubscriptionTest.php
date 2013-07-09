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
        $subscription = new Subscription();

        $this->assertNull(
            $subscription->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $subscription->getStatus(),
            '"status" should initially be null'
        );

        $this->assertNull(
            $subscription->getYear(),
            '"year" should initially be null'
        );

        $this->assertNull(
            $subscription->getCollege(),
            '"college" should initially be null'
        );

        $this->assertNull(
            $subscription->getStudy(),
            '"study" should initially be null'
        );

        $this->assertNull(
            $subscription->getPaymentMethod(),
            '"paymentMethod" should initially be null'
        );

        $this->assertNull(
            $subscription->getObservation(),
            '"observation" should initially be null'
        );

        $this->assertNull(
            $subscription->getObservation(),
            '"observation" should initially be null'
        );

        $this->assertNull(
            $subscription->getDigitalSignature(),
            '"digitalSignature" should initially be null'
        );

        $this->assertNull(
            $subscription->getDigitalSignatureTitle(),
            '"digitalSignatureTitle" should initially be null'
        );

        $this->assertNull(
            $subscription->getDigitalSignatureTitle(),
            '"digitalSignatureTitle" should initially be null'
        );

        $this->assertNull(
            $subscription->getPaymentSystemName(),
            '"paymentSystemName" should initially be null'
        );

        $this->assertNull(
            $subscription->getCreated(),
            '"created" should initially be null'
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

        $subscription->setDigitalSignature('John Doe');
        $this->assertEquals('John Doe', $subscription->getDigitalSignature());

        $subscription->setDigitalSignatureTitle('Tester');
        $this->assertEquals('Tester', $subscription->getDigitalSignatureTitle());

        $subscription->setPaymentAmount(100);
        $this->assertEquals(100, $subscription->getPaymentAmount());

        $collegeMock = $this->getMock('Mrss\Entity\College');
        $subscription->setCollege($collegeMock);
        $this->assertSame($collegeMock, $subscription->getCollege());

        $studyMock = $this->getMock('Mrss\Entity\Study');
        $subscription->setStudy($studyMock);
        $this->assertSame($studyMock, $subscription->getStudy());

        $obMock = $this->getMock('Mrss\Entity\Observation');
        $subscription->setObservation($obMock);
        $this->assertSame($obMock, $subscription->getObservation());

        $subscription->setPaymentSystemName('SUNY');
        $this->assertEquals('SUNY', $subscription->getPaymentSystemName());

        $subscription->setCreated('2013-07-09');
        $this->assertEquals('2013-07-09', $subscription->getCreated());
    }
}
