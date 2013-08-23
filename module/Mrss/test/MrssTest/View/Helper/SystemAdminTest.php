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

    public function testSetUser()
    {
        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array()
        );

        $this->helper->setUser($userMock);
        $this->assertInstanceOf('Mrss\Entity\User', $this->helper->getUser());
    }

    public function testGetColleges()
    {
        $optionCollegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getSubscriptionByStudyAndYear')
        );
        $optionCollegeMock->expects($this->once())
            ->method('getSubscriptionByStudyAndYear')
            ->will($this->returnValue(null));

        $optionCollegeMock2 = $this->getMock(
            'Mrss\Entity\College',
            array('getSubscriptionByStudyAndYear')
        );
        $subscriptionMock = $this->getMock(
            'Mrss\Entity\Subscription',
            array()
        );
        $optionCollegeMock2->expects($this->once())
            ->method('getSubscriptionByStudyAndYear')
            ->will($this->returnValue($subscriptionMock));


        $systemMock = $this->getMock(
            'Mrss\Entity\System',
            array('getColleges')
        );
        $systemMock->expects($this->once())
            ->method('getColleges')
            ->will(
                $this->returnValue(
                    array(
                        $optionCollegeMock,
                        $optionCollegeMock2
                    )
                )
            );

        $collegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getSystem')
        );
        $collegeMock->expects($this->once())
            ->method('getSystem')
            ->will($this->returnValue($systemMock));

        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array('getCollege')
        );

        $userMock->expects($this->once())
            ->method('getCollege')
            ->will($this->returnValue($collegeMock));

        $this->helper->setUser($userMock);

        // Current study mock
        $currentStudyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getId', 'getCurrentYear')
        );

        // Current study plugin
        $currentStudyPluginMock = $this->getMock(
            'Mrss\Controller\Plugin\CurrentStudy',
            array('getCurrentStudy')
        );
        $currentStudyPluginMock->expects($this->any())
            ->method('getCurrentStudy')
            ->will($this->returnValue($currentStudyMock));

        $this->helper->setCurrentStudyPlugin($currentStudyPluginMock);

        $colleges = $this->helper->getColleges();
        $this->assertTrue(is_array($colleges));
        $this->assertEquals(1, count($colleges));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage System not found
     */
    public function testGetCollegesException()
    {
        $collegeMock = $this->getMock(
            'Mrss\Entity\College',
            array('getSystem')
        );
        $collegeMock->expects($this->once())
            ->method('getSystem')
            ->will($this->returnValue(null));

        $userMock = $this->getMock(
            'Mrss\Entity\User',
            array('getCollege')
        );

        $userMock->expects($this->once())
            ->method('getCollege')
            ->will($this->returnValue($collegeMock));

        $this->helper->setUser($userMock);

        $colleges = $this->helper->getColleges();
    }

    public function testGetOverviewUrl()
    {
        $viewMock = $this->getMock(
            'Zend\View\Renderer\ConsoleRenderer',
            array('url')
        );
        $viewMock->expects($this->once())
            ->method('url')
            ->will($this->returnValue('/data-entry'));

        $this->helper->setView($viewMock);

        $this->assertEquals('/data-entry', $this->helper->getOverviewUrl());
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
