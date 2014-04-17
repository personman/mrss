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
    /** @var Agreement */
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
        $offerCodes = array('test');
        $form = new Agreement('MRSS', $offerCodes);
        $spec = $form->getInputFilterSpecification();

        $this->assertTrue(is_array($spec));
    }

    public function testConstructionWithCodes()
    {
        $offerCodes = array('test');
        $form = new Agreement('MRSS', $offerCodes);

        $this->assertNotEmpty($form->get('offerCode'));
    }

    public function testConstructionWithoutCodes()
    {
        $this->assertFalse($this->form->has('offerCode'));
    }
}
