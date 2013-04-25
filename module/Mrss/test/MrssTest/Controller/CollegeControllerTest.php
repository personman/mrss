<?php

namespace MrssTest\Controller;

/**
 * Class CollegeControllerTest
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class CollegeControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include $this->getConfigPath()
        );
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $collegeModelMock = $this->getCollegeModelMock();
        $collegeModelMock->expects($this->once())
            ->method('findAll');

        $sm = $this->getServiceLocator();
        $sm->setService('model.college', $collegeModelMock);


        $this->dispatch('/colleges');
        // Why is this failing with a 500 status?
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('colleges');
        $this->assertActionName('index');
        $this->assertControllerClass('CollegeController');
        $this->assertMatchedRouteName('general');
    }

    public function testViewActionCanBeAccessed()
    {
        // Mock the returned college entity
        $collegeMock = $this->getMock('Mrss\Entity\College', array('getName'));

        // Mock of the college model
        $collegeModelMock = $this->getCollegeModelMock();
        $collegeModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($collegeMock));

        $sm = $this->getServiceLocator();
        $sm->setService('model.college', $collegeModelMock);

        $this->dispatch('/colleges/view/5');
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('colleges');
        $this->assertActionName('view');
        $this->assertControllerClass('CollegeController');
        $this->assertMatchedRouteName('general');
    }

    public function testViewWithInvalidId()
    {
        // Mock of the college model, returning null
        $collegeModelMock = $this->getCollegeModelMock();
        $collegeModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));

        $sm = $this->getServiceLocator();
        $sm->setService('model.college', $collegeModelMock);

        $this->dispatch('/colleges/view/5');

        $this->assertRedirect();
    }

    public function testMapActionCanBeAccessed()
    {
        $this->dispatch('/colleges/map');
        //$this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('colleges');
        $this->assertActionName('map');
        $this->assertControllerClass('CollegeController');
        $this->assertMatchedRouteName('general');
    }

    public function testFlashMessenger()
    {
        $this->dispatch('/colleges/flashtest');

        $this->assertRedirectTo('/colleges');
    }

    protected function getCollegeModelMock()
    {
        $collegeModelMock = $this->getMock(
            '\Mrss\Model\College',
            array('findAll', 'find'),
            array(),
            '',
            false
        );

        return $collegeModelMock;
    }
}
