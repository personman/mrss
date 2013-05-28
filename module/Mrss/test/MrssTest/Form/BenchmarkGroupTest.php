<?php

namespace MrssTest\Form;

use Mrss\Form\BenchmarkGroup;
use PHPUnit_Framework_TestCase;

/**
 * Class BenchmarkGroupTest
 *
 * @package MrssTest\Form
 */
class BenchmarkGroupTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new BenchmarkGroup();
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\BenchmarkGroup', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('name'));
        $this->assertNotEmpty($this->form->get('description'));
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('shortName'));
    }
}
