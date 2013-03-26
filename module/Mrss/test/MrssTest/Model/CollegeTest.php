<?php
/**
 * Test the college entity
 */
namespace MrssTest\Model;

use Mrss\Model\College;
use PHPUnit_Framework_TestCase;

/**
 * Class CollegeTest
 *
 * @package MrssTest\Model
 */
class CollegeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testCollegeInitialState()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $college = new College();

        $this->assertNull($college->name, '"name" should initially be null');
        $this->assertNull($college->id, '"id" should initially be null');
        $this->assertNull($college->city, '"city" should initially be null');
    }

    /**
     * Test that the class can be populated with array data.
     *
     * @return null
     */
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

        $this->assertSame($data['name'], $college->name, '"name" not set correctly');
        $this->assertSame($data['id'], $college->id, '"id" was not set correctly');
        $this->assertSame(
            $data['ipeds'],
            $college->ipeds,
            '"ipeds" not set correctly'
        );
        $this->assertSame($data['city'], $college->city, '"city" not set correctly');
    }

    /**
     * Test that the data array can nullify properties.
     *
     * @return null
     */
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
