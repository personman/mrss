<?php

namespace MrssTest\Form;

use Mrss\Form\ImportData;
use PHPUnit_Framework_TestCase;

/**
 * Class ImportDataTest
 *
 * @package MrssTest\Form
 */
class ImportDataTest extends PHPUnit_Framework_TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new ImportData('import');
    }

    public function testFormConstruction()
    {
        $this->assertInstanceOf('Mrss\Form\ImportData', $this->form);
    }
}
