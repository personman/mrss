<?php

namespace MrssTest\Controller;

/**
 * Class ObservationControllerTest
 *
 * @package MrssTest\Controller
 */
class ObservationControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include $this->getConfigPath()
        );
        parent::setUp();
    }

    /**
     * It takes a lot of mocking to get all the way through the view.
     * Is there a way to disable view rendering and just inspect the viewModel
     * directly?
     */
    public function testViewActionCanBeAccessed()
    {
        // Mock the college
        $collegeMock = $this->getMock('Mrss\Entity\College', array('getName'));

        // Mock the observation entity
        $observationMock = $this->getMock(
            'Mrss\Entity\Observation',
            array('getCollege')
        );
        $observationMock->expects($this->once())
            ->method('getCollege')
            ->will($this->returnValue($collegeMock));

        // Mock the model
        $observationModelMock = $this->getMock(
            '\Mrss\Model\Observation',
            array('find'),
            array(),
            '',
            false
        );
        $observationModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($observationMock));


        // Put the mocks in the service locator
        $sm = $this->getServiceLocator();
        $sm->setService('model.observation', $observationModelMock);

        $this->dispatch('/observations/view/5');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('observations');
        $this->assertActionName('view');
        $this->assertControllerClass('ObservationController');
        $this->assertMatchedRouteName('general');
    }
}
