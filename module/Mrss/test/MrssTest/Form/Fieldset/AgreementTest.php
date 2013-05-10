<?php

namespace MrssTest\Form\Fieldset;

use Mrss\Form\Fieldset\Agreement;
use PHPUnit_Framework_TestCase;

/**
 * Class AgreementTest
 *
 * @package MrssTest\Form
 */
class AgreementTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Agreement();
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Fieldset\Agreement', $this->form);
    }

    public function testGetInputFilterSpecification()
    {
        $spec = $this->form->getInputFilterSpecification();

        $this->assertTrue(is_array($spec));
    }
}
