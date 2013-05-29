<?php

namespace MrssTest\Validator;

use Mrss\Validator\Equation;
use PHPUnit_Framework_TestCase;

class EquationTest extends PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $computedFieldsMock;
    protected $benchmarkModelMock;

    public function setUp()
    {
        $this->computedFieldMock = $this->getMock(
            'Mrss\Service\ComputedFields',
            array('getVariables')
        );

        $this->benchmarkModelMock = $this->getMock(
            'Mrss\Model\Benchmark',
            array('findOneByDbColumn')
        );

        $this->validator = new Equation(
            $this->computedFieldMock,
            $this->benchmarkModelMock
        );
    }

    public function testIsValidNoVariables()
    {
        $this->computedFieldMock->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue(array()));

        $equation = "1 + 2";
        $context = array('inputType' => 'computed');

        $this->assertTrue(
            $this->validator->isValid($equation, $context)
        );
    }

    public function testEmptyEquationComputed()
    {
        $equation = '';
        $context = array('inputType' => 'computed');

        $this->assertFalse(
            $this->validator->isValid($equation, $context)
        );
    }

    public function testEmptyEquationNonComputed()
    {
        $equation = '';
        $context = array('inputType' => 'number');

        $this->assertTrue(
            $this->validator->isValid($equation, $context)
        );
    }

    public function testUnknownVariable()
    {
        $variables = array('notReal');

        $this->computedFieldMock->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $this->benchmarkModelMock->expects($this->any())
            ->method('findOneByDbColumn')
            ->will($this->returnValue(null));

        $equation = "{{notReal}} + 1";
        $context = array('inputType' => 'computed');

        $this->assertFalse(
            $this->validator->isValid($equation, $context)
        );
    }

    public function testIsCalculable()
    {
        $variables = array('test');

        $this->computedFieldMock->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $this->benchmarkModelMock->expects($this->any())
            ->method('findOneByDbColumn')
            ->will($this->returnValue(1));


        $equation = "{{test}} + 1 + 2";
        $context = array('inputType' => 'computed');

        $this->assertTrue(
            $this->validator->isValid($equation, $context)
        );

    }

    public function testIsNotCalculable()
    {
        $variables = array('test');

        $this->computedFieldMock->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $this->benchmarkModelMock->expects($this->any())
            ->method('findOneByDbColumn')
            ->will($this->returnValue(1));


        $equation = "{{test}} + 1 + 2 badVariable";
        $context = array('inputType' => 'computed');

        $this->assertFalse(
            $this->validator->isValid($equation, $context)
        );

    }
}
