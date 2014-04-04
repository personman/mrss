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

        // Set computed
        $benchmark->setComputed(true);
        $this->assertTrue($benchmark->getComputed());

        // Set equation
        $benchmark->setEquation("5 + 3");
        $this->assertEquals("5 + 3", $benchmark->getEquation());

        // Set options
        $benchmark->setOptions(array('one', 'two'));
        $this->assertEquals(array('one', 'two'), $benchmark->getOptions());

        // Set exclude
        $benchmark->setExcludeFromCompletion(true);
        $this->assertTrue($benchmark->getExcludeFromCompletion());
        $benchmark->setExcludeFromCompletion(false);
        $this->assertFalse($benchmark->getExcludeFromCompletion());
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

    public function testGetFormElementDollars()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('dollars');

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
        $this->assertEquals('\d+(\.\d+)?', $formElement['attributes']['pattern']);
        $this->assertEquals(
            'Use the format 1234 or 1234.56',
            $formElement['attributes']['title']
        );
    }

    public function testGetFormElementPercent()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('percent');

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
        $this->assertEquals('\d+(\.\d+)?', $formElement['attributes']['pattern']);
        $this->assertEquals(
            'Use the format 12, 12.3 or 12.34',
            $formElement['attributes']['title']
        );
    }

    public function testGetFormElementWholePercent()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('wholepercent');

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
        $this->assertEquals('\d+', $formElement['attributes']['pattern']);
        $this->assertEquals(
            'Use a whole number (no decimals)',
            $formElement['attributes']['title']
        );
    }

    public function testGetFormElementNumber()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('number');

        $formElement = $benchmark->getFormElement();

        $this->assertTrue(is_array($formElement));
        $this->assertEquals('\d+', $formElement['attributes']['pattern']);
        $this->assertEquals(
            'Use the format 1234',
            $formElement['attributes']['title']
        );
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

    public function testGetFormElementInputFilterWholeDollars()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('wholedollars');

        $filter = $benchmark->getFormElementInputFilter();
        $this->assertEquals('Regex', $filter['validators'][0]['name']);
    }

    public function testGetFormElementInputFilterPercent()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('percent');

        $filter = $benchmark->getFormElementInputFilter();
        $this->assertEquals('Regex', $filter['validators'][0]['name']);
    }

    public function testGetFormElementInputFilterWholePercent()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('wholepercent');

        $filter = $benchmark->getFormElementInputFilter();
        $this->assertEquals('Regex', $filter['validators'][0]['name']);
    }

    public function testGetFormElementInputFilterFloat()
    {
        $benchmark = new Benchmark;
        $benchmark->setInputType('float');

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

    public function testIsPercent()
    {
        $benchmark = new Benchmark();

        $this->assertFalse($benchmark->isPercent());

        $benchmark->setInputType('percent');
        $this->assertTrue($benchmark->isPercent());

        $benchmark->setInputType('dollars');
        $this->assertFalse($benchmark->isPercent());

        $benchmark->setInputType('wholepercent');
        $this->assertTrue($benchmark->isPercent());
    }

    public function testIsDollars()
    {
        $benchmark = new Benchmark();

        $this->assertFalse($benchmark->isDollars());

        $benchmark->setInputType('dollars');
        $this->assertTrue($benchmark->isDollars());

        $benchmark->setInputType('percent');
        $this->assertFalse($benchmark->isDollars());

        $benchmark->setInputType('wholedollars');
        $this->assertTrue($benchmark->isDollars());
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
