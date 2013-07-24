<?php

namespace MrssTest\Form;

use Mrss\Form\SystemAdmin;
use PHPUnit_Framework_TestCase;

/**
 * Class SystemAdminTest
 *
 * @package MrssTest\Form
 */
class SystemAdminTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array('getId', 'getFullName')
        );
        $userMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $userMock->expects($this->once())
            ->method('getFullName')
            ->will($this->returnValue('John Doe'));
        $users = array($userMock);

        $collegeMock = $this->getMock(
            'Mrss\Enity\College',
            array('getId', 'getName', 'getUsers')
        );
        $collegeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $collegeMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('JCCC'));
        $collegeMock->expects($this->once())
            ->method('getUsers')
            ->will($this->returnValue($users));
        $colleges = array($collegeMock);

        $systemMock = $this->getMock(
            'Mrss\Entity\System',
            array('getColleges')
        );
        $systemMock->expects($this->once())
            ->method('getColleges')
            ->will($this->returnValue($colleges));

        $this->form = new SystemAdmin($systemMock);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\SystemAdmin', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('system_id'));
        $this->assertNotEmpty($this->form->get('user_id'));
    }
}
