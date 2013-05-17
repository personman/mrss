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
        $this->form = new Payment(5, 100);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Payment', $this->form);
    }
}
