<?php

namespace MrssTest\Form;

use Mrss\Form\PeerComparison;
use PHPUnit_Framework_TestCase;

/**
 * Class PeerComparisonTest
 *
 * @package MrssTest\Form
 */
class PeerComparisonTest extends PHPUnit_Framework_TestCase
{
    /** @var  PeerComparison */
    protected $form;

    public function setUp()
    {
        $yearsWithData = range(2005, 2007);
        $this->form = new PeerComparison($yearsWithData);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\PeerComparison', $this->form);

        // Make sure the elements are present
        $this->assertNotEmpty($this->form->get('reportingPeriod'));
        $this->assertNotEmpty($this->form->get('benchmarks'));
        $this->assertNotEmpty($this->form->get('peers'));
    }
}
