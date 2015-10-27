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
        $studyMock = $this->getMock('Mrss\Entity\Study');
        $studyMock->expects($this->any())
            ->method('getCriteria')
            ->will($this->returnValue(array()));

        $this->form = new PeerComparisonDemographics($studyMock);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\PeerComparisonDemographics', $this->form);
    }
}
