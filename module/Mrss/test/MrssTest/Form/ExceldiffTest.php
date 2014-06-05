<?php

namespace MrssTest\Form;

use Mrss\Form\Exceldiff;
use PHPUnit_Framework_TestCase;

/**
 * Class ExceldiffTest
 *
 * @package MrssTest\Form
 */
class ExceldiffTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Exceldiff('import');
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Exceldiff', $this->form);
    }
}
