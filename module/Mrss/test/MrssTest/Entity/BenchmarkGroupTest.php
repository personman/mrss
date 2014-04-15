<?php

namespace MrssTest\Entity;

use Mrss\Entity\BenchmarkGroup;
use PHPUnit_Framework_TestCase;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Entity
 */
class BenchmarkGroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testUserInitialState()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $this->assertNull(
            $benchmarkGroup->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getName(),
            '"name" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getShortName(),
            '"shortName" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getDescription(),
            '"description" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getSequence(),
            '"sequence" should initially be null'
        );

        $this->assertNull(
            $benchmarkGroup->getStudy(),
            '"study" should initially be null'
        );

        $filter = $benchmarkGroup->getInputFilter();
        $this->assertInstanceOf('Zend\InputFilter\InputFilterInterface', $filter);
    }

    public function testSetters()
    {
        $benchmarkGroup = new BenchmarkGroup;

        // Set Label
        $benchmarkGroup->setName('Financial Data');
        $this->assertEquals('Financial Data', $benchmarkGroup->getName());

        // Set shortName
        $benchmarkGroup->setShortName('form_1');
        $this->assertEquals('form_1', $benchmarkGroup->getShortName());

        // Set description
        $benchmarkGroup->setDescription('lorem ipsum');
        $this->assertEquals('lorem ipsum', $benchmarkGroup->getDescription());

        // Set sequence
        $benchmarkGroup->setSequence(2);
        $this->assertEquals(2, $benchmarkGroup->getSequence());

        // Set format
        $benchmarkGroup->setFormat('one-col');
        $this->assertEquals('one-col', $benchmarkGroup->getFormat());

        // Set use sub ob
        $benchmarkGroup->setUseSubObservation(true);
        $this->assertTrue($benchmarkGroup->getUseSubObservation());

        $studyMock = $this->getMock('Mrss\Entity\Study');
        $benchmarkGroup->setStudy($studyMock);
        $this->assertSame($studyMock, $benchmarkGroup->getStudy());

        $inputFilterMock = $this->getMock(
            'Zend\InputFilter\InputFilterInterface'
        );
        $benchmarkGroup->setInputFilter($inputFilterMock);
        $this->assertSame($inputFilterMock, $benchmarkGroup->getInputFilter());
    }

    public function testBenchmarkAssociation()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $benchmarkGroup->getBenchmarks()
        );
    }

    public function testGetElements()
    {
        $benchmarkGroup = new BenchmarkGroup;

        // Mock up a benchmark to attach
        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('isAvailableForYear', 'getInputType')
        );
        $benchmarkMock->expects($this->once())
            ->method('isAvailableForYear')
            ->will($this->returnValue(true));

        $benchmarkGroup->setBenchmarks(array($benchmarkMock));

        $this->assertTrue(is_array($benchmarkGroup->getElements(2013)));
    }

    public function testGetLabel()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $benchmarkGroup->setName('Form 18');

        $this->assertEquals('Form 18', $benchmarkGroup->getLabel());
    }

    public function testGetCompletionPercentageForObservation()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $observationMock = $this->getMock(
            'Mrss\Entity\Observation',
            array('get')
        );

        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getDbColumn')
        );

        // First test with no benchmarks
        $percentage = $benchmarkGroup
            ->getCompletionPercentageForObservation($observationMock);

        $this->assertEquals(0, $percentage);


        // Then again with mock benchmark
        $benchmarkGroup->setBenchmarks(array($benchmarkMock));

        $percentage = $benchmarkGroup
            ->getCompletionPercentageForObservation($observationMock);

        $this->assertEquals(0, $percentage);

        // And once more with an actual non-null value
        $observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue('555'));

        $percentage = $benchmarkGroup
            ->getCompletionPercentageForObservation($observationMock);

        $this->assertEquals(100.0, $percentage);
    }

    public function testCountCompleteFieldsInSubobservations()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $benchmarkMock = $this->getMock(
            '\Mrss\Entity\Benchmark',
            array('isAvailableForYear')
        );
        $benchmarkMock->expects($this->any())
            ->method('isAvailableForYear')
            ->will($this->returnValue(true));

        $benchmarkGroup->setBenchmarks(array($benchmarkMock));


        $observationMock = $this->getMock(
            '\Mrss\Entity\Observation',
            array('getSubObservations')
        );

        $subobMock = $this->getMock(
            '\Mrss\Entity\SubObservation'
        );
        $subobMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(5));

        $observationMock->expects($this->any())
            ->method('getSubObservations')
            ->will($this->returnValue(array($subobMock)));

        $benchmarkGroup->countCompleteFieldsInSubobservations($observationMock);

        $benchmarkGroup->getSubObservationBenchmarkCount($observationMock);

        $benchmarkGroup->getSubObservationCompletionPercentageForObservation(
            $observationMock
        );
    }

    public function testCountCompleteFieldsInSubobservationsEmpty()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $observationMock = $this->getMock(
            '\Mrss\Entity\Observation',
            array('getSubObservations')
        );

        $percentage = $benchmarkGroup->getSubObservationCompletionPercentageForObservation(
            $observationMock
        );

        $this->assertEquals(0, $percentage);
    }

    public function testGetBenchmarkCountSubOb()
    {
        $benchmarkGroup = new BenchmarkGroup;
        $benchmarkGroup->setUseSubObservation(true);

        $benchmarkMock = $this->getMock(
            '\Mrss\Entity\Benchmark',
            array('isAvailableForYear')
        );
        $benchmarkMock->expects($this->any())
            ->method('isAvailableForYear')
            ->will($this->returnValue(true));

        $benchmarkGroup->setBenchmarks(array($benchmarkMock));


        $observationMock = $this->getMock(
            '\Mrss\Entity\Observation',
            array('getSubObservations')
        );

        $subobMock = $this->getMock(
            '\Mrss\Entity\SubObservation'
        );
        $subobMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(5));

        $observationMock->expects($this->any())
            ->method('getSubObservations')
            ->will($this->returnValue(array($subobMock)));

        $count = $benchmarkGroup->getBenchmarkCount($observationMock);
        $this->assertEquals(1, $count);

        $count = $benchmarkGroup->countCompleteFieldsInObservation($observationMock);
        $this->assertEquals(1, $count);
    }

    public function testGetIncompleteBenchmarksForObservation()
    {
        $benchmarkGroup = new BenchmarkGroup;

        $observationMock = $this->getMock(
            '\Mrss\Entity\Observation',
            array('getYear', 'get')
        );
        $observationMock->expects($this->any())
            ->method('getYear')
            ->will($this->returnValue(2014));

        $observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $benchmarkMock = $this->getMock(
            '\Mrss\Entity\Benchmark',
            array('getDbColumn', 'isAvailableForYear')
        );
        $benchmarkMock->expects($this->any())
            ->method('getDbColumn')
            ->will($this->returnValue('blah'));

        $benchmarkMock->expects($this->any())
            ->method('isAvailableForYear')
            ->will($this->returnValue(true));

        $benchmarkGroup->setBenchmarks(array($benchmarkMock));

        $benchmarks = $benchmarkGroup
            ->getIncompleteBenchmarksForObservation($observationMock);

        $this->assertEquals(1, count($benchmarks));

    }
}
