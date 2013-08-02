<?php

namespace MrssTest\Form;

use Mrss\Form\Page;
use PHPUnit_Framework_TestCase;

/**
 * Class PageTest
 *
 * @package CmsTest\Form
 */
class PageTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Page($this->getEmMock());
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Page', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('title'));
        $this->assertNotEmpty($this->form->get('content'));
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('status'));
    }

    protected function getEmMock()
    {
        $repositoryMock = $this->getMock(
            'Doctrine\Orm\Repository',
            array('findOneBy')
        );

        $emMock  = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array(
                'getRepository',
                'getClassMetadata',
                'persist',
                'flush',
                'remove'
            ),
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }
}
