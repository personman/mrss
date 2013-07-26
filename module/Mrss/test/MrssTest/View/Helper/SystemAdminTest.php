<?php

namespace MrssTest\View\Helper;

use Mrss\View\Helper\SystemAdmin;
use PHPUnit_Framework_TestCase;

class SystemAdminTest extends PHPUnit_Framework_TestCase
{
    /** @var SystemAdmin  */
    protected $helper;

    public function setUp()
    {
        $this->helper = new SystemAdmin();
    }

    public function testClass()
    {
        $this->assertInstanceOf('Mrss\View\Helper\SystemAdmin', $this->helper);
    }

    /*public function testSetPlugin()
    {
        // College list
        $collegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getId', 'getName')
        );

        // System mock
        $systemMock = $this->getMock(
            'Mrss\Entity\System',
            array('getColleges')
        );
        $systemMock->expects($this->once())
            ->method('getColleges')
            ->will($this->returnValue(array($collegeMock)));

        // Prepare the active college
        $activeCollegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getName', 'getSystem')
        );
        $activeCollegeMock->expects($this->once())
            ->method('getSystem')
            ->will($this->returnValue($systemMock));

        // Form helper
        $formHelperMock = $this->getMock(
            'Zend\Form\View\Helper\Form',
            array('formRow')
        );

        // Mock the view
        $viewMock = $this->getMock(
            'Zend\View\Renderer\PhpRenderer',
            array('form')
        );
        $viewMock->expects($this->any())
            ->method('form')
            ->will($this->returnValue($formHelperMock));
        $this->helper->setView($viewMock);

        // Inject the plugin
        $pluginMock = $this->getMock(
            'Mrss\Controller\Plugin\SystemActiveCollege',
            array('getActiveCollege')
        );

        $pluginMock->expects($this->once())
            ->method('getActiveCollege')
            ->will($this->returnValue($activeCollegeMock));

        $this->helper->setActiveCollegePlugin($pluginMock);

        // Inject the user
        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array('getRole', 'getCollege')
        );
        $userMock->expects($this->once())
            ->method('getRole')
            ->will($this->returnValue('system_admin'));
        $userMock->expects($this->once())
            ->method('getCollege')
            ->will($this->returnValue($activeCollegeMock));
        $this->helper->setUser($userMock);

        // Invoke
        $helper = $this->helper;
        $this->assertEquals('placeholder', $helper());
    }*/
}
