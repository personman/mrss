<?php
/**
 * Test the Change entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Change;
use Mrss\Entity\ChangeSet;
use Mrss\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class ChangeTest
 *
 * @package MrssTest\Model
 */
class ChangeTest extends PHPUnit_Framework_TestCase
{
    /** @var  Change */
    protected $change;

    public function setUp()
    {
        $this->change = new Change;
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\Change', $this->change);
    }

    public function testSetters()
    {
        $this->change->setId(1);
        $this->assertEquals(1, $this->change->getId());

        $changeSet = $this->getChangeSetMock();
        $this->change->setChangeSet($changeSet);
        $this->assertSame($changeSet, $this->change->getChangeSet());

        $this->change->setOldValue(5);
        $this->assertEquals(5, $this->change->getOldValue());

        $this->change->setNewValue(10);
        $this->assertEquals(10, $this->change->getNewValue());
    }

    protected function getChangeSetMock()
    {
        $changeSet = $this->getMock('\Mrss\Entity\ChangeSet');

        return $changeSet;
    }
}
