<?php
/**
 * Test the college entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\College;
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

        $this->assertNull($college->getName(), '"name" should initially be null');
        //$this->assertNull($college->id, '"id" should initially be null');
        //$this->assertNull($college->city, '"city" should initially be null');
    }
}
