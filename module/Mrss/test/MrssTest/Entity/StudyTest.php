<?php
/**
 * Test the study entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Study;
use PHPUnit_Framework_TestCase;

/**
 * Class StudyTest
 *
 * @package MrssTest\Model
 */
class StudyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Study
     */
    protected $study;

    public function setUp()
    {
        $this->study = new Study;
    }

    public function tearDown()
    {
        unset($this->study);
    }

    public function testinitialState()
    {
        $this->assertNull($this->study->getId());
        $this->assertNull($this->study->getName());
        $this->assertNull($this->study->getDescription());

        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $this->study->getBenchmarkGroups()
        );
    }

    public function testSetters()
    {
        $this->study->setName('NCCBP');
        $this->assertEquals('NCCBP', $this->study->getName());

        $this->study->setDescription('lorem');
        $this->assertEquals('lorem', $this->study->getDescription());

        $groupsMock = array('placeholder');
        $this->study->setBenchmarkGroups($groupsMock);
        $this->assertEquals($groupsMock, $this->study->getBenchmarkGroups());
    }

    public function testCompletionPercentage()
    {
        $observationMock = $this->getMock('Mrss\Entity\Observation');

        // Test it with no benchmarkGroups
        $percentage = $this->study->getCompletionPercentage($observationMock);
        $this->assertEquals(0, $percentage);

        // Now test it with benchmarkGroups
        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getBenchmarks', 'countCompleteFieldsInObservation')
        );
        $benchmarkGroupMock->expects($this->once())
            ->method('getBenchmarks')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $this->study->setBenchmarkGroups(array($benchmarkGroupMock));

        $percentage = $this->study->getCompletionPercentage($observationMock);
        $this->assertEquals(0, $percentage);
    }
}
