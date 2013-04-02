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

        $this->assertNull(
            $college->getName(),
            '"name" should initially be null'
        );

        $this->assertNull(
            $college->getIpeds(),
            '"ipeds" should initially be null'
        );

        $this->assertNull(
            $college->getAddress(),
            '"address" should initially be null'
        );

        $this->assertNull(
            $college->getCity(),
            '"city" should initially be null'
        );

        $this->assertNull(
            $college->getState(),
            '"state" should initially be null'
        );

        $this->assertNull(
            $college->getZip(),
            '"zip" should initially be null'
        );
    }

    /**
     * Can we set college properties and do they stick?
     *
     * @dataProvider getCollegeData
     * @param array $collegeData
     */
    public function testSetters($collegeData)
    {
        $college = new College;

        $college->setName($collegeData['name']);
        $college->setIpeds($collegeData['ipeds']);
        $college->setAddress($collegeData['address']);
        $college->setCity($collegeData['city']);
        $college->setState($collegeData['state']);
        $college->setZip($collegeData['zip']);

        $this->assertEquals($collegeData['name'], $college->getName());
        $this->assertEquals($collegeData['ipeds'], $college->getIpeds());
        $this->assertEquals($collegeData['address'], $college->getAddress());
        $this->assertEquals($collegeData['city'], $college->getCity());
        $this->assertEquals($collegeData['state'], $college->getState());
        $this->assertEquals($collegeData['zip'], $college->getZip());
    }

    /**
     * Provides some valid college data
     *
     * @return array
     */
    public function getCollegeData()
    {
        return array(
            array(
                array(
                    'name' => 'Highland Community College',
                    'ipeds' => '192100',
                    'address' => '606 West Main Street',
                    'city' => 'Highland',
                    'state' => 'KS',
                    'zip' => 66035
                )
            )
        );
    }
}
