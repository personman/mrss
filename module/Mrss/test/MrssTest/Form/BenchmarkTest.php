<?php

namespace MrssTest\Form;

use Mrss\Form\Benchmark;
use PHPUnit_Framework_TestCase;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Form
 */
class BenchmarkTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Benchmark;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Benchmark', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('name'));
        $this->assertNotEmpty($this->form->get('description'));
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('inputType'));
    }
}
