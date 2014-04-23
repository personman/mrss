<?php
/**
 * Test the ChangeSet entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Change;
use Mrss\Entity\ChangeSet;
use Mrss\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class ChangeSetTest
 *
 * @package MrssTest\Model
 */
class ChangeSetTest extends PHPUnit_Framework_TestCase
{
    /** @var  ChangeSet */
    protected $changeSet;

    public function setUp()
    {
        $this->changeSet = new ChangeSet;
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\ChangeSet', $this->changeSet);
    }

    public function testSetters()
    {
        $this->changeSet->setId(1);
        $this->assertEquals(1, $this->changeSet->getId());

        $user = new User;
        $this->changeSet->setUser($user);
        $this->assertSame($user, $this->changeSet->getUser());

        $impersonator = new User;
        $this->changeSet->setImpersonatingUser($impersonator);
        $this->assertSame($impersonator, $this->changeSet->getImpersonatingUser());

        $date = new \DateTime('now');
        $this->changeSet->setDate($date);
        $this->assertSame($date, $this->changeSet->getDate());

        $changes = array(
            new Change,
            new Change,
            new Change
        );
        $this->changeSet->setChanges($changes);
        $changesBack = $this->changeSet->getChanges();
        $this->assertSame($changes[0], $changesBack[0]);
    }
}
