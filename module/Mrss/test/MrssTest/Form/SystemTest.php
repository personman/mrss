<?php

namespace MrssTest\Form;

use Mrss\Form\System;
use PHPUnit_Framework_TestCase;

/**
 * Class SystemTest
 *
 * @package MrssTest\Form
 */
class SystemTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new System;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\System', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('name'));
        $this->assertNotEmpty($this->form->get('ipeds'));
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('address'));
    }
}
