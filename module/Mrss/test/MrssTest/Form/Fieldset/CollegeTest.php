<?php

namespace MrssTest\Form\Fieldset;

use Mrss\Form\Fieldset\College;
use PHPUnit_Framework_TestCase;

/**
 * Class AgreementTest
 *
 * @package MrssTest\Form
 */
class CollegeTest extends PHPUnit_Framework_TestCase
{
    /** @var College */
    protected $form;

    public function setUp()
    {
        $this->form = new College(true);
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\Fieldset\College', $this->form);
    }
}
