<?php

namespace MrssTest\Form;

use Mrss\Form\SubscriptionInvoice;
use PHPUnit_Framework_TestCase;

/**
 * Class SubscriptionInvoiceTest
 *
 * @package MrssTest\Form
 */
class SubscriptionInvoiceTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new SubscriptionInvoice;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\SubscriptionInvoice', $this->form);
    }
}
