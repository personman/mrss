<?php

namespace MrssTest\Validator;

use Mrss\Validator\MinimumSelected;
use PHPUnit_Framework_TestCase;

class MinimumSelectedTest extends PHPUnit_Framework_TestCase
{
    protected $validator;

    public function setUp()
    {
        $this->validator = new MinimumSelected(2);
    }

    public function testIsValid()
    {
        $value = array(1, 2, 3);

        $this->assertTrue(
            $this->validator->isValid($value)
        );
    }

    public function testNotValid()
    {
        $value = array(1);

        $this->assertFalse(
            $this->validator->isValid($value)
        );
    }
}
