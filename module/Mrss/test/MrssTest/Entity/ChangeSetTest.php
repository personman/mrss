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

        $observation = $this->getMock('\Mrss\Entity\Observation');
        $this->changeSet->setObservation($observation);
        $this->assertSame($observation, $this->changeSet->getObservation());

        $study = $this->getMock('\Mrss\Entity\Study');
        $this->changeSet->setStudy($study);
        $this->assertSame($study, $this->changeSet->getStudy());

        $this->changeSet->setEditType('dataEntry');
        $this->assertEquals('dataEntry', $this->changeSet->getEditType());

        $subobservation = $this->getMock('\Mrss\Entity\SubObservation');
        $this->changeSet->setSubObservation($subobservation);
        $this->assertSame($subobservation, $this->changeSet->getSubObservation());

    }

    /**
     * @param $editType
     * @param $label
     * @dataProvider getEditLabels
     */
    public function testEditTypeLabel($editType, $label)
    {
        $this->changeSet->setEditType($editType);
        $this->assertEquals($label, $this->changeSet->getEditTypeLabel());
    }

    public function getEditLabels()
    {
        return array(
            array('dataEntry', 'data entry form'),
            array('excel', 'Excel upload'),
            array('adminEdit', 'admin edit form'),
            array('fakeOne', 'fakeOne')
        );
    }
}
