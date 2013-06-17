<?php

namespace MrssTest\Form;

use Mrss\Form\Payment;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest
 *
 * @package MrssTest\Form
 */
class PaymentTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Payment(5, 'http://test.com', 100);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Payment', $this->form);
    }

    public function testEmptyConstructionParam()
    {
        $this->setExpectedException('\Exception');
        $form = new Payment(null, 'http://test.com', 100);
    }

    public function testEmptyConstructionParamTwo()
    {
        $this->setExpectedException('\Exception');
        $form = new Payment(5, null, 100);
    }
}
