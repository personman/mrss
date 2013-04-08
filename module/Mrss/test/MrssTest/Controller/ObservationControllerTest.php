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
            ->method('getCollege', 'get')
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


        // Mock the benchmark entity
        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getDbColumn', 'getName')
        );
        $benchmarkMock->expects($this->once())
            ->method('getDbColumn')
            ->will($this->returnValue('tot_fte_counc_adv_staff'));
        $benchmarkMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('blee'));

        // Mock the benchmark model
        $benchmarkModelMock = $this->getMock(
            '\Mrss\Model\Benchmark',
            array('findAll'),
            array(),
            '',
            false
        );
        $benchmarkModelMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array($benchmarkMock)));


        // Put the mocks in the service locator
        $sm = $this->getServiceLocator();
        $sm->setService('model.observation', $observationModelMock);
        $sm->setService('model.benchmark', $benchmarkModelMock);

        $this->dispatch('/observations/view/5');
        $r = $this->getResponse()->getContent();
        //\Zend\Debug\Debug::dump($r);die;
        //echo $r; die;
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('observations');
        $this->assertActionName('view');
        $this->assertControllerClass('ObservationController');
        $this->assertMatchedRouteName('general');
    }
}
