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
    public function testInitialState()
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

        $this->assertNull(
            $benchmark->getEquation(),
            "'equation' should initially be null"
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

        // Set equation
        $benchmark->setEquation("5 + 3");
        $this->assertEquals("5 + 3", $benchmark->getEquation());

        // Set options
        $benchmark->setOptions(array('one', 'two'));
        $this->assertEquals(array('one', 'two'), $benchmark->getOptions());
    }

    public function testAssociationMethods()
    {
        // Mock a benchmark group for insertion
        $benchmarkGroupMock = $this->getMock('Mrss\Entity\BenchmarkGroup');

        $benchmark = new Benchmark;

        $benchmark->setBenchmarkGroup($benchmarkGroupMock);

        $this->assertSame($benchmarkGroupMock, $benchmark->getBenchmarkGroup());


        // Benchmark years
        $years = array(2013);
        $benchmark->setYearsAvailable($years);

        $result = $benchmark->getYearsAvailable();
        $this->assertSame($years, $result);
    }

    public function testGetFormElement()
    {
        $benchmark = new Benchmark;

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
    }

    public function testGetFormElementInputFilterNumber()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('number');

        $filter = $benchmark->getFormElementInputFilter();
        $this->assertEquals('Digits', $filter['validators'][0]['name']);
    }

    public function testGetFormElementInputFilterDollars()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('dollars');

        $filter = $benchmark->getFormElementInputFilter();
        $this->assertEquals('Regex', $filter['validators'][0]['name']);
    }

    public function testInputFilter()
    {
        $benchmark = new Benchmark;
        $benchmark->setEntityManager($this->getEmMock());

        $this->assertInstanceOf(
            'Zend\InputFilter\InputFilterInterface',
            $benchmark->getInputFilter()
        );
    }

    public function testSetInputFilter()
    {
        $benchmark = new Benchmark;
        $benchmark->setEntityManager($this->getEmMock());

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

    public function testSetEquationValidator()
    {
        $benchmark = new Benchmark;
        $benchmark->setEntityManager($this->getEmMock());

        $equationValidatorMock = $this->getMock(
            'Mrss\Validator\Equation',
            array(),
            array(),
            '',
            false
        );

        $benchmark->setEquationValidator($equationValidatorMock);

        $this->assertInstanceOf(
            'Zend\InputFilter\InputFilterInterface',
            $benchmark->getInputFilter()
        );
    }

    public function testGetCompletionPercentage()
    {
        $benchmark = new Benchmark;

        $percentages = array(2010 => 50);

        $benchmarkModelMock = $this->getMock(
            'Mrss\Model\Benchmark',
            array('getCompletionPercentages')
        );

        $benchmarkModelMock->expects($this->once())
            ->method('getCompletionPercentages')
            ->will($this->returnValue($percentages));

        $benchmark->setBenchmarkModel($benchmarkModelMock);

        $this->assertEquals(50, $benchmark->getCompletionPercentage(2010));
        $this->assertEquals(null, $benchmark->getCompletionPercentage(2012));
    }

    public function testGetRadioInput()
    {
        $benchmark = new Benchmark;

        $benchmark->setInputType('radio');
        $element = $benchmark->getFormElement();

        $this->assertEquals('Select', $element['type']);
    }

    protected function getEmMock($additionalMethodsToMock = array())
    {
        $repositoryMock = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array('findOneBy', 'find', 'findAll', 'findBy', 'getClassName')
        );

        $methodsToMock = array(
            'getRepository',
            'getClassMetadata',
            'persist',
            'flush'
        );
        $methodsToMock = array_merge($methodsToMock, $additionalMethodsToMock);

        $emMock  = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            $methodsToMock,
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }
}
