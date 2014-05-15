<?php
/**
 * Test the subscriptionDraft entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\SubscriptionDraft;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
 * Class SubscriptionDraftTest
 *
 * @package MrssTest\Entity
 */
class SubscriptionDraftTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubscriptionDraft
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new SubscriptionDraft();
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Entity\SubscriptionDraft', $this->entity);

        $this->assertNull($this->entity->getId());
        $this->assertNull($this->entity->getFormData());
        $this->assertNull($this->entity->getAgreementData());
        $this->assertNull($this->entity->getDate());
        $this->assertNull($this->entity->getIp());
    }

    public function testSetters()
    {
        $this->entity->setId(5);
        $this->assertEquals(5, $this->entity->getId());

        $this->entity->setFormData('lorem ipsum');
        $this->assertEquals('lorem ipsum', $this->entity->getFormData());

        $this->entity->setAgreementData('blah blah');
        $this->assertEquals('blah blah', $this->entity->getAgreementData());

        $date = new DateTime('now');
        $this->entity->setDate($date);
        $this->assertSame($date, $this->entity->getDate());

        $ip = '192.168.1.1';
        $this->entity->setIp($ip);
        $this->assertEquals($ip, $this->entity->getIp());
    }
}
