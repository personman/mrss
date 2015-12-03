<?php

namespace MrssTest\Service;

use Mrss\Service\FCSValidation;
use PHPUnit_Framework_TestCase;

/**
 * Class FCSValidationTest
 *
 * @package MrssTest\Service
 */
class FCSValidationTest extends PHPUnit_Framework_TestCase
{
    /** @var  FCSValidation */
    protected $validator;

    protected $observationMock;

    protected function setUp()
    {
        $this->validator = new FCSValidation();

        // Mock the observation
        $this->observationMock = $this->getMock(
            'Mrss\Entity\Observation',
            array('get')
        );

        $this->validator->setObservation($this->observationMock);
    }

    public function testGetMethodNames()
    {
        $names = $this->validator->getMethodNames();

        $this->assertTrue(in_array('validateExecCompensation', $names));
    }

    public function testValidateSalariesEnteredSuccess()
    {
        $this->observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(100));

        $this->validator->validateSalariesEntered();
        $messages = $this->validator->getIssues();

        $hasMessages = (count($messages) > 0);
        $this->assertFalse($hasMessages);
    }

    public function testValidateSalariesEnteredFailure()
    {
        $this->observationMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $this->validator->validateSalariesEntered();
        $messages = $this->validator->getIssues();

        $hasMessages = (count($messages) > 0);
        $this->assertTrue($hasMessages);
    }

    public function testValidateExecCompensation()
    {
        $this->observationMock->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('ft_president_salary'))
            ->will($this->returnValue(1500000));

        $this->observationMock->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('ft_president_supplemental'))
            ->will($this->returnValue(50000));

        $this->validator->validateExecCompensation();
        $messages = $this->validator->getIssues();

        $hasMessages = (count($messages) > 0);
        $this->assertTrue($hasMessages);
    }

    public function testValidateExecCompensationMin()
    {
        $this->observationMock->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('ft_president_salary'))
            ->will($this->returnValue(15000));

        $this->observationMock->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('ft_president_supplemental'))
            ->will($this->returnValue(500));

        $this->validator->validateExecCompensation();
        $messages = $this->validator->getIssues();

        $hasMessages = (count($messages) > 0);
        $this->assertTrue($hasMessages);
    }
}
