<?php

namespace MrssTest\View\Helper;

use Mrss\View\Helper\CurrentStudy;
use PHPUnit_Framework_TestCase;

class CurrentStudyTest extends PHPUnit_Framework_TestCase
{
    /** @var CurrentStudy  */
    protected $helper;

    public function setUp()
    {
        $this->helper = new CurrentStudy;
    }

    public function testSetPlugin()
    {
        $currentStudyPluginMock = $this->getMock(
            'Mrss\Controller\Plugin\CurrentStudy',
            array('getCurrentStudy')
        );

        $currentStudyPluginMock->expects($this->once())
            ->method('getCurrentStudy')
            ->will($this->returnValue('placeholder'));

        $this->helper->setPlugin($currentStudyPluginMock);

        // Invoke
        $helper = $this->helper;
        $this->assertEquals('placeholder', $helper());
    }
}
