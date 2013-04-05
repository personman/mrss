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
    public function testIndexActionCanBeAccessed()
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

        $this->dispatch('/import');

        // It should redirect:
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('mrss');
        $this->assertControllerName('import');
        $this->assertActionName('index');
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
            array('importObservations'),
            array(),
            '',
            false
        );

        $importerMock->expects($this->once())
            ->method('importObservations');

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
}
