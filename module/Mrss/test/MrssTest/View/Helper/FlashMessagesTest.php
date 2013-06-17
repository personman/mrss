<?php

namespace MrssTest\View\Helper;

use Mrss\View\Helper\FlashMessages;
use PHPUnit_Framework_TestCase;

class FlashMessagesTest extends PHPUnit_Framework_TestCase
{
    /** @var FlashMessages  */
    protected $helper;

    public function setUp()
    {
        $this->helper = new FlashMessages();
    }

    public function testSetPlugin()
    {
        $flashMessagesPluginMock = $this->getMock(
            'Zend\Mvc\Controller\Plugin\FlashMessenger',
            array(
                'getMessagesFromNamespace',
                'getCurrentMessagesFromNamespace',
                'clearCurrentMessagesFromNamespace'
            )
        );

        $flashMessagesPluginMock->expects($this->any())
            ->method('getCurrentMessagesFromNamespace')
            ->will($this->returnValue(array()));

        $flashMessagesPluginMock->expects($this->any())
            ->method('getMessagesFromNamespace')
            ->will($this->returnValue(array()));

        $this->helper->setFlashMessenger($flashMessagesPluginMock);

        // Invoke
        $helper = $this->helper;
        $this->assertEquals($this->getEmptyMessageArray(), $helper(true));
    }

    public function getEmptyMessageArray()
    {
        return array(
            'error' => array(),
            'success' => array(),
            'info' => array(),
            'default' => array()
        );
    }
}
