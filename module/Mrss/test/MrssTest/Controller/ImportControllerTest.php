<?php

namespace MrssTest\Controller;

/**
 * Class ImportControllerTest
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class ImportControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include $this->getConfigPath()
        );

        parent::setUp();
    }

    /**
     * Test import action for colleges
     */
    public function testCollegesActionCanBeAccessed()
    {
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importColleges'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importColleges');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        $this->dispatch('/import/colleges');

        // It should redirect:
        $this->assertRedirectTo('/colleges');

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('colleges');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    /**
     * Test import action for observations
     */
    public function testObsActionCanBeAccessed()
    {
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importAllObservations'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importAllObservations');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        // Make sure sm will hand that mock back to us
        $importer = $sm->get('import.nccbp');
        $this->assertSame($importerMock, $importer);

        // Dispatch the request
        $this->dispatch('/import/obs');

        // It should redirect:
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('obs');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    public function testMetaAction()
    {
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importFieldMetadata'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importFieldMetadata');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        // Make sure sm will hand that mock back to us
        $importer = $sm->get('import.nccbp');
        $this->assertSame($importerMock, $importer);

        // Dispatch the request
        $this->dispatch('/import/meta');

        // It should redirect:
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('meta');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    public function testIndexAction()
    {
        // Mock some dependencies:
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('getImports'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('getImports')
            ->will($this->returnValue($this->getImports()));

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        $this->dispatch('/import');

        // It should redirect:
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('index');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    public function testBackgroundAction()
    {
        // Mock some dependencies:
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importColleges'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importColleges');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        // Dispatch
        $this->dispatch('/import/background', 'GET', array('type' => 'colleges'));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('background');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    public function testBackgroundActionWithInvalidType()
    {
        // Mock some dependencies:
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('getImports'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('getImports')
            ->will($this->returnValue($this->getImports()));

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        $this->dispatch('/import/background', 'GET', array('type' => 'not-real'));
        $this->assertResponseStatusCode(500);
    }

    public function testBackgroundActionWithInvalidImportMethod()
    {
        // Mock the importer
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('getImports'),
            array(),
            '',
            false
        );

        $badImports = array(
            'colleges' => array(
                'method' => 'aMethodThatDoesNotExist'
            )
        );

        $importerMock->expects($this->once())
            ->method('getImports')
            ->will($this->returnValue($badImports));

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);
        $this->dispatch('/import/background', 'GET', array('type' => 'colleges'));
        $this->assertResponseStatusCode(500);
    }

    public function testTriggerAction()
    {
        // Mock some dependencies:
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('importColleges'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importColleges');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        // Dispatch
        $this->dispatch('/import/trigger', 'GET', array('type' => 'colleges'));
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('trigger');
        $this->assertControllerClass('ImportController');
        $this->assertMatchedRouteName('general');
    }

    /**
     * Giving a non-real import type should land you on an error page
     */
    public function testTriggerActionWithInvalidType()
    {
        // Mock some dependencies:
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('getImports'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('getImports')
            ->will($this->returnValue($this->getImports()));

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);
        $this->dispatch('/import/trigger', 'GET', array('type' => 'not-real'));
        $this->assertResponseStatusCode(500);
    }

    public function testProgressAction()
    {
        $importerMock = $this->getMock(
            '\Mrss\Service\ImportNccbp',
            array('getProgress'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('getProgress');

        $sm = $this->getServiceLocator();
        $sm->setService('import.nccbp', $importerMock);

        $this->dispatch('/import/progress');

        $this->assertResponseStatusCode(200);
    }

    protected function getImports()
    {
        return array(
            'colleges' => array(
                'label' => 'Colleges',
                'method' => 'importColleges'
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'method' => 'importFieldMetadata'
            ),
            'observations' => array(
                'label' => 'Observations',
                'method' => 'importAllObservations'
            )
        );
    }
}
