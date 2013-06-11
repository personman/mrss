<?php

namespace MrssTest\Controller\Plugin;

use Mrss\Controller\Plugin\CurrentStudy;
use PHPUnit_Framework_TestCase;

class CurrentStudyTest extends PHPUnit_Framework_TestCase
{
    public function testGetCurrentStudy()
    {
        $plugin = new CurrentStudy();

        $config = array(
            'maximizingresources.com' => 2
        );
        $plugin->setConfig($config);

        $url = 'maximizingresources.com';
        $plugin->setUrl($url);

        $studyMock = $this->getMock(
            'Mrss\Entity\Study'
        );

        $studyModelMock = $this->getMock(
            'Mrss\Model\Study',
            array('find')
        );
        $studyModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($studyMock));
        $plugin->setStudyModel($studyModelMock);

        // Invoke
        $this->assertSame($studyMock, $plugin());
    }

    /**
     * @expectedException Exception
     */
    public function testGetCurrentStudyEmpty()
    {
        $plugin = new CurrentStudy();

        $config = array(
            'maximizingresources.com' => 2
        );
        $plugin->setConfig($config);

        $url = 'maximizingresources.com';
        $plugin->setUrl($url);

        $studyModelMock = $this->getMock(
            'Mrss\Model\Study',
            array('find')
        );

        // No study found, expect exception
        $studyModelMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));
        $plugin->setStudyModel($studyModelMock);

        // Invoke
        $plugin();
    }

    /**
     * @expectedException Exception
     */
    public function testGetCurrentStudyNoConfigMatch()
    {
        $plugin = new CurrentStudy();

        $config = array(
        );
        $plugin->setConfig($config);

        $url = 'maximizingresources.com';
        $plugin->setUrl($url);

        // Invoke
        $plugin();
    }
}
