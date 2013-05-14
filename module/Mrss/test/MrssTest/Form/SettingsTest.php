<?php

namespace MrssTest\Form;

use Mrss\Form\Settings;
use PHPUnit_Framework_TestCase;

/**
 * Class SettingsTest
 *
 * @package MrssTest\Form
 */
class SettingsTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Settings;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Settings', $this->form);
    }
}
