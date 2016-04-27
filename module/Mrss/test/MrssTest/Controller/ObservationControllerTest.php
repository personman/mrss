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

    public function testDummy()
    {
        $this->assertTrue(true);
    }

    /**
     * It takes a lot of mocking to get all the way through the view.
     * Is there a way to disable view rendering and just inspect the viewModel
     * directly?
     *
     * This is probably a sign that the controller action is too complex.
     */
    /*public function testViewActionCanBeAccessed()
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

        // Mock the benchmarkGroup model
        $benchmarkGroupModelMock = $this->getMock(
            '\Mrss\Model\BenchmarkGroup',
            array('find', 'findAll'),
            array(),
            '',
            false
        );
        $benchmarkGroupModelMock
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array()));

        // Put the mocks in the service locator
        $sm = $this->getServiceLocator();
        $sm->setService('model.observation', $observationModelMock);
        $sm->setService('model.benchmark', $benchmarkModelMock);
        $sm->setService('model.benchmark.group', $benchmarkGroupModelMock);

        $this->dispatch('/observations/5');
        //print($this->getResponse()->getContent());die('viewActionCan');
        $this->assertResponseStatusCode(200);


        $this->assertModuleName('mrss');
        $this->assertControllerName('observations');
        $this->assertActionName('view');
        $this->assertControllerClass('ObservationController');
        $this->assertMatchedRouteName('observation');
    }

    public function testEditCanBeAccessed()
    {

        // Mock the college
        $collegeMock = $this->getMock('Mrss\Entity\College', array('getName'));
        $collegeMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Some name'));

        // Mock the observation entity
        $observationMock = $this->getMock(
            'Mrss\Entity\Observation',
            array('getCollege', 'getYear')
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

        // Mock the benchmark model
        $benchmarkModelMock = $this->getMock(
            '\Mrss\Model\Benchmark',
            array('findAll'),
            array(),
            '',
            false
        );

        // Form
        $formMock = $this->getMock(
            'Zend\Form\Form',
            array('setAttribute')
        );

        // Form service
        $formServiceMock = $this->getMock(
            'Mrss\Service\FormBuilder',
            array('buildForm')
        );

        $formServiceMock->expects($this->once())
            ->method('buildForm')
            ->will($this->returnValue($formMock));

        // Benchmark group
        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup'
        );

        // Benchmark group model
        $benchmarkGroupModelMock = $this->getMock(
            '\Mrss\Model\BenchmarkGroup',
            array('find'),
            array(),
            '',
            false
        );
        $benchmarkGroupModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($benchmarkGroupMock));


        // Put the mocks in the service locator
        $sm = $this->getServiceLocator();
        $sm->setService('model.observation', $observationModelMock);
        $sm->setService('model.benchmark', $benchmarkModelMock);
        $sm->setService('model.benchmark.group', $benchmarkGroupModelMock);
        $sm->setService('service.formBuilder', $formServiceMock);
        $this->dispatch('/observations/edit/5');

        //echo $this->getResponse()->getContent(); die;

        $this->assertResponseStatusCode(200);



        $this->assertModuleName('mrss');
        $this->assertControllerName('observations');
        $this->assertActionName('edit');
        $this->assertControllerClass('ObservationController');
        $this->assertMatchedRouteName('general');
    }*/
}
