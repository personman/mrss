<?php

namespace MrssTest\Form;

use Mrss\Form\OfferCode;
use PHPUnit_Framework_TestCase;

/**
 * Class OfferCodeTest
 *
 * @package MrssTest\Form
 */
class OfferCodeTest extends PHPUnit_Framework_TestCase
{
    /** @var  OfferCode */
    protected $form;

    public function setUp()
    {
        $this->form = new OfferCode;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\OfferCode', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('id'));
        $this->assertNotEmpty($this->form->get('study'));
        $this->assertNotEmpty($this->form->get('price'));
        $this->assertNotEmpty($this->form->get('skipOtherDiscounts'));
    }
}
