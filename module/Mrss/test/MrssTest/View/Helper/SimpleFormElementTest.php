<?php

namespace MrssTest\View\Helper;

use Mrss\View\Helper\SimpleFormElement;
use PHPUnit_Framework_TestCase;
use Zend\Form\Form;

class SimpleFormElementTest extends PHPUnit_Framework_TestCase
{
    /** @var SimpleFormElement  */
    protected $helper;

    public function setUp()
    {
        $this->helper = new SimpleFormElement();
    }

    public function testInvoke()
    {
        $pluginMock = $this->getMock(
            'Zend\View\AbstractPlugin',
            array('openTag', 'closeTag')
        );
        $pluginManagerMock = $this->getMock(
            'Zend\View\HelperPluginManager',
            array('get')
        );

        $pluginManagerMock->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('formLabel'))
            ->will($this->returnValue($pluginMock));

        $pluginManagerMock->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('formInput'))
            ->will($this->returnValue('placeholder'));

        $viewMock = new \Zend\View\Renderer\PhpRenderer;
        $viewMock->setHelperPluginManager($pluginManagerMock);

        $this->helper->setView($viewMock);

        $elementMock = $this->getMock(
            'Zend\View\Form\Element',
            array('getMessages')
        );
        $elementMock->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(array('test')));

        $helper = $this->helper;

        $result = $helper($elementMock);
    }
}
