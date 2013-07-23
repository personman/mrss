<?php

namespace MrssTest\Form;

use Mrss\Form\SystemCollege;
use PHPUnit_Framework_TestCase;

/**
 * Class SystemCollegeTest
 *
 * @package MrssTest\Form
 */
class SystemCollegeTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $collegeMock = $this->getMock(
            'Mrss\Enity\College',
            array('getId', 'getName')
        );
        $collegeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $collegeMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('JCCC'));
        $colleges = array($collegeMock);

        $collegeModelMock = $this->getMock(
            'Mrss\Model\College',
            array('findAll')
        );
        $collegeModelMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($colleges));

        $this->form = new SystemCollege($collegeModelMock);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\SystemCollege', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('system_id'));
        $this->assertNotEmpty($this->form->get('college_id'));
    }
}
