<?php

namespace MrssTest\Form;

use Mrss\Form\Study;
use PHPUnit_Framework_TestCase;

/**
 * Class StudyTest
 *
 * @package MrssTest\Form
 */
class StudyTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Study;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Study', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('name'));
        $this->assertNotEmpty($this->form->get('description'));
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('currentYear'));
    }
}
