<?php
/**
 * Test the settings entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Setting;
use PHPUnit_Framework_TestCase;

/**
 * Class SettingTest
 *
 * @package MrssTest\Model
 */
class SettingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Mrss\Entity\Setting
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new Setting;
    }

    public function testInitialState()
    {
        $this->assertNull($this->entity->getIdentifier());
        $this->assertNull($this->entity->getValue());
    }

    public function testSetters()
    {
        $this->entity->setIdentifier('test');
        $this->assertEquals('test', $this->entity->getIdentifier());

        $this->entity->setValue('lorem');
        $this->assertEquals('lorem', $this->entity->getValue());
    }
}
