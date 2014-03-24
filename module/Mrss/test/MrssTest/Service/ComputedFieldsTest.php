<?php

namespace MrssTest\Service;

use Mrss\Service\ComputedFields;
use MrssTest\TestCase;

class ComputedFieldsTest extends TestCase
{
    /** @var ComputedFields  */
    protected $computedFields;

    protected $benchmarkMock;
    protected $observationMock;
    protected $observationModelMock;

    public function setUp()
    {
        $this->computedFields = new ComputedFields();

        $this->benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getEquation', 'getDbColumn')
        );

        $this->observationMock = $this->getMock(
            'Mrss\Entity\Observation',
            array('set', 'get')
        );

        $this->observationModelMock = $this->getMock(
            'Mrss\Model\Observation',
            array('save', 'flush', 'getEntityManager')
        );

        $this->observationModelMock->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->getEmMock()));

        $this->computedFields->setObservationModel($this->observationModelMock);
    }

    /**
     * If there's no equation, it should bail early
     */
    public function testCalculateNoEquation()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue(''));

        $this->assertFalse(
            $this->computedFields->calculate(
                $this->benchmarkMock,
                $this->observationMock
            )
        );
    }

    /**
     * If the equation can't be parsed, throw an exception
     *
     * @expectedException \exprlib\exceptions\UnknownTokenException
     */
    public function testCalculateInvalidEquation()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('not an equation'));

        $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );
    }

    public function testCalculateValidEquation()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('2 + 2'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->observationMock->expects($this->once())
            ->method('set')
            ->with('my_test_column', 4);

        $this->observationModelMock->expects($this->once())
            ->method('save');

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertTrue($result);
    }

    public function testCalculateEquationWithVariables()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('2 + {{another_test_column}}'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->observationMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue(8));

        $this->observationMock->expects($this->once())
            ->method('set')
            ->with('my_test_column', 10);

        $this->observationModelMock->expects($this->once())
            ->method('save');


        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertTrue($result);
    }

    public function testCalculateUnknownVariable()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('2 + {{unknown_test_column}}'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->observationMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertFalse($result);
    }

    public function testCalculateAllForObservation()
    {
        $computedBenchmarkMocks = array(
            $this->benchmarkMock
        );

        $benchmarkModelMock = $this->getMock(
            'Mrss\Model\Benchmark',
            array('findComputed')
        );
        $benchmarkModelMock->expects($this->once())
            ->method('findComputed')
            ->will($this->returnValue($computedBenchmarkMocks));

        $this->computedFields->setBenchmarkModel($benchmarkModelMock);

        $this->computedFields->calculateAllForObservation(
            $this->observationMock
        );
    }
}
