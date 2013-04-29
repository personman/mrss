<?php

namespace MrssTest\Entity;

use Mrss\Entity\Benchmark;
use PHPUnit_Framework_TestCase;
use ZendTest\Di\TestAsset\SetterInjection\B;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Entity
 */
class BenchmarkTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testUserInitialState()
    {
        $benchmark = new Benchmark;

        $this->assertNull(
            $benchmark->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $benchmark->getName(),
            '"name" should initially be null'
        );

        $this->assertNull(
            $benchmark->getDescription(),
            '"description" should initially be null'
        );

        $this->assertNull(
            $benchmark->getDbColumn(),
            '"dbColumn" should initially be null'
        );

        $this->assertNull(
            $benchmark->getSequence(),
            '"sequence" should initially be null'
        );

        $this->assertNull(
            $benchmark->getInputType(),
            '"inputType" should initially be null'
        );

        $this->assertNull(
            $benchmark->getStatus(),
            '"status" should initially be null'
        );
    }

    public function testSetters()
    {
        $benchmark = new Benchmark;

        // Set name
        $benchmark->setName('Transfer rate');
        $this->assertEquals('Transfer rate', $benchmark->getName());

        // Set description
        $benchmark->setDescription('lorem ipsum');
        $this->assertEquals('lorem ipsum', $benchmark->getDescription());

        // Set dbColumn
        $benchmark->setDbColumn('transfer_rate');
        $this->assertEquals('transfer_rate', $benchmark->getDbColumn());

        // Set sequence
        $benchmark->setSequence(2);
        $this->assertEquals(2, $benchmark->getSequence());

        // Set input type
        $benchmark->setInputType('text');
        $this->assertEquals('text', $benchmark->getInputType());

        // Set status
        $benchmark->setStatus(1);
        $this->assertEquals(1, $benchmark->getStatus());
    }

    public function testAssociationMethods()
    {
        // Mock a benchmark group for insertion
        $benchmarkGroupMock = $this->getMock('Mrss\Entity\BenchmarkGroup');

        $benchmark = new Benchmark;

        $benchmark->setBenchmarkGroup($benchmarkGroupMock);

        $this->assertSame($benchmarkGroupMock, $benchmark->getBenchmarkGroup());


        // Benchmark year
        $yearMock = $this->getMock('Mrss\Entity\BenchmarkYear');
        $benchmark->setYears(array($yearMock));

        $result = $benchmark->getYears();
        $this->assertSame($yearMock, $result[0]);
    }

    public function testGetFormElement()
    {
        $benchmark = new Benchmark;

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
    }

    public function testInputFilter()
    {
        $benchmark = new Benchmark;

        $this->assertInstanceOf(
            'Zend\InputFilter\InputFilterInterface',
            $benchmark->getInputFilter()
        );
    }

    public function testSetInputFilter()
    {
        $benchmark = new Benchmark;

        $inputFilterMock = $this->getMock(
            'Zend\InputFilter\BaseInputFilter',
            array('add')
        );

        $benchmark->setInputFilter($inputFilterMock);

        $this->assertInstanceOf(
            'Zend\InputFilter\InputFilterInterface',
            $benchmark->getInputFilter()
        );
    }
}
