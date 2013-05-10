<?php

namespace MrssTest\Form;

use Mrss\Form\SubscriptionSystem;
use PHPUnit_Framework_TestCase;

/**
 * Class SubscriptionSystemTest
 *
 * @package MrssTest\Form
 */
class SubscriptionSystemTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new SubscriptionSystem;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\SubscriptionSystem', $this->form);
    }
}
