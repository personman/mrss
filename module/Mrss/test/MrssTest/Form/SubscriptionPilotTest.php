<?php

namespace MrssTest\Form;

use Mrss\Form\SubscriptionPilot;
use PHPUnit_Framework_TestCase;

/**
 * Class SubscriptionPilotTest
 *
 * @package MrssTest\Form
 */
class SubscriptionPilotTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new SubscriptionPilot();
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\SubscriptionPilot', $this->form);
    }
}
