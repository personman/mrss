<?php

namespace MrssTest\Form;

use Mrss\Form\Subscription;
use PHPUnit_Framework_TestCase;

/**
 * Class SubscriptionTest
 *
 * @package MrssTest\Form
 */
class SubscriptionTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new Subscription();
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Subscription', $this->form);

        // Make sure the fieldsets are present
        $this->assertNotEmpty($this->form->get('institution'));
        $this->assertNotEmpty($this->form->get('adminContact'));
        $this->assertNotEmpty($this->form->get('dataContact'));
    }

    public function testInputFilters()
    {
        $institution = $this->form->get('institution');
        $this->assertNotEmpty($institution->getInputFilterSpecification());

        $fieldset = $this->form->get('adminContact');
        $this->assertNotEmpty($fieldset->getInputFilterSpecification());

        $fieldset = $this->form->get('dataContact');
        $this->assertNotEmpty($fieldset->getInputFilterSpecification());

    }
}
