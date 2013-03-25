<?php

namespace MrssTest\Model;

use Mrss\Model\College;
use PHPUnit_Framework_TestCase;

class CollegeTest extends PHPUnit_Framework_TestCase
{
    public function testCollegeInitialState()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $college = new College();

        $this->assertNull($college->name, '"name" should initially be null');
        $this->assertNull($college->id, '"id" should initially be null');
        $this->assertNull($college->city, '"city" should initially be null');

    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $college = new College();
        $data = array(
            'name' => 'Johnson County Community College',
            'id' => 123,
            'ipeds' => '155210',
            'city' => 'Overland Park, KS'
        );

        $college->exchangeArray($data);

        $this->assertSame($data['name'], $college->name, '"name" was not set correctly');
        $this->assertSame($data['id'], $college->id, '"id" was not set correctly');
        $this->assertSame($data['ipeds'], $college->ipeds, '"ipeds" was not set correctly');
        $this->assertSame($data['city'], $college->city, '"city" was not set correctly');
    }


    public function testExchangeArraySetsPropertiesToNullIfKeysAreNotPresent()
    {
        $college = new College();

        $data = array(
            'name' => 'Johnson County Community College',
            'id' => 123,
            'ipeds' => '155210',
            'city' => 'Overland Park, KS'
        );

        // Once with some data
        $college->exchangeArray($data);

        // Now nullify it:
        $college->exchangeArray(array());

        $this->assertNull($college->name, '"name" should have defaulted to null');
        $this->assertNull($college->id, '"id" should have defaulted to null');
        $this->assertNull($college->ipeds, '"ipeds" should have defaulted to null');
        $this->assertNull($college->city, '"city" should have defaulted to null');
    }

}