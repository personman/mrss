<?php

namespace MrssTest\Form;

use Mrss\Form\PeerComparisonDemographics;
use PHPUnit_Framework_TestCase;

/**
 * Class PeerComparisonDemographicsTest
 *
 * @package MrssTest\Form
 */
class PeerComparisonDemographicsTest extends PHPUnit_Framework_TestCase
{
    /** @var  PeerComparisonDemographics */
    protected $form;

    public function setUp()
    {
        $this->form = new PeerComparisonDemographics;
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\PeerComparisonDemographics', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('states'));
        $this->assertNotEmpty($this->form->get('environments'));
        $this->assertNotEmpty($this->form->get('workforceEnrollment'));
        $this->assertNotEmpty($this->form->get('workforceRevenue'));
    }
}
