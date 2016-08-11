<?php

namespace MrssTest\Service;

use Mrss\Service\ComputedFields;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;
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
            array('getEquation', 'getDbColumn', 'getInputType')
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

        $benchmarkModelMock = $this->getMock(
            'Mrss\Model\Benchmark',
            array('findComputed')
        );
        $benchmarkModelMock->expects($this->any())
            ->method('findComputed')
            ->will($this->returnValue(array()));

        $this->computedFields->setBenchmarkModel($benchmarkModelMock);

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(4));
        $this->computedFields->setStudy($studyMock);



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

        $this->assertEquals(
            null,
            $this->computedFields->calculate(
                $this->benchmarkMock,
                $this->observationMock
            )
        );
    }

    /**
     * If the equation can't be parsed, throw an exception
     *
     */
    public function testCalculateInvalidEquation()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('not an equation'));

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertEquals(null, $result);
    }

    public function testCalculateValidEquation()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('2 + 2'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->benchmarkMock->expects($this->once())
            ->method('getInputType')
            ->will($this->returnValue('percent'));

        $this->observationMock->expects($this->once())
            ->method('set')
            ->with('my_test_column', 400);

        $this->observationModelMock->expects($this->once())
            ->method('save');

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertEquals(400, $result);
    }

    /*public function testCalculateEquationWithVariables()
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

        $this->assertEquals(10, $result);
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
            ->will($this->returnValue(4));

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertEquals(2, $result);
    }*/

    public function testCalculateComparison()
    {
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('2 < 5'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertEquals(true, $result);
    }

    public function testCalculateMixedAddSub()
    {
        // Doesn't work without the middle 2 parens
        $this->benchmarkMock->expects($this->once())
            ->method('getEquation')
            ->will($this->returnValue('( 6475 - 434) + (9341 - 432 )'));

        $this->benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('my_test_column'));

        $this->observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $result = $this->computedFields->calculate(
            $this->benchmarkMock,
            $this->observationMock
        );

        $this->assertEquals(14950, $result);
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

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(4));


        $this->computedFields->calculateAllForObservation(
            $this->observationMock
        );
    }

    /*public function testNestedEquation()
    {
        $equation = "{{inst_full_expend}} / {{inst_full_num}}";

        $computedBenchmark = new Benchmark;
        $computedBenchmark->setEquation("{{inst_part_expend}} / {{inst_part_num}}");
        $computedBenchmark->setDbColumn("inst_full_num");

        $computedBenchmarkMocks = array(
            $computedBenchmark
        );

        $benchmarkModelMock = $this->getMock(
            'Mrss\Model\Benchmark',
            array('findComputed')
        );
        $benchmarkModelMock->expects($this->once())
            ->method('findComputed')
            ->will($this->returnValue($computedBenchmarkMocks));

        $this->computedFields->setBenchmarkModel($benchmarkModelMock);


        $observation = new Observation;
        $observation->set('inst_full_expend', 10);
        $observation->set('inst_full_num', 20);


        $result = $this->computedFields->nestComputedEquations($equation, 2014);

        $expected = "{{inst_full_expend}} / ( {{inst_part_expend}} / {{inst_part_num}} )";

        $this->assertEquals($expected, $result);
    }

    public function testCheckEquation()
    {
        // Valid
        $equation = "{{inst_full_expend}} / {{inst_full_num}}";
        $result = $this->computedFields->checkEquation($equation);

        $this->assertEquals(true, $result);

        // Invalid
        $equation = "{{inst_full_exp}} / {{inst_full_num}}";
        $result = $this->computedFields->checkEquation($equation);

        $this->assertEquals(false, $result);

        // Parse error
        $equation = "{{inst_full_expend}} / {{inst_full_num}} /+)";
        $result = $this->computedFields->checkEquation($equation);

        $this->assertEquals(false, $result);

    }*/

    public function testComparisons()
    {
        // Greater than
        $equation = "5 > 4";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(true, $result);

        $equation = "3 > 4";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(false, $result);

        // Less than
        $equation = "5 < 4";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(false, $result);

        $equation = "3 < 4";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(true, $result);

        // Equals
        $equation = "4 = 4";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(true, $result);
    }

    public function testMax()
    {
        $equation = "max(3, 4, 5, 0)";

        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(5, $result);

        $equation = "(max(3, 4, 5)) + 5";

        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(10, $result);

        // Two maxes
        $equation = "(max(3, 4, 5)) + (max(7, 8, 9))";
        $parsedEquation = $this->computedFields->buildEquation($equation);
        $result = $parsedEquation->evaluate();

        $this->assertEquals(14, $result);

    }
}
