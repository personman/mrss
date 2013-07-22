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
            $college->getId(),
            '"id" should initially be null'
        );

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

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $college->getObservations()
        );

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $college->getUsers()
        );

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $college->getSubscriptions()
        );

        $this->assertNull(
            $college->getSystem(),
            '"system" should initially be null'
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
        $college->setAddress2($collegeData['address2']);
        $college->setCity($collegeData['city']);
        $college->setState($collegeData['state']);
        $college->setZip($collegeData['zip']);
        $college->setSystem($this->getMock('Mrss\Entity\System'));

        $this->assertEquals($collegeData['name'], $college->getName());
        $this->assertEquals($collegeData['ipeds'], $college->getIpeds());
        $this->assertEquals($collegeData['address'], $college->getAddress());
        $this->assertEquals($collegeData['address2'], $college->getAddress2());
        $this->assertEquals($collegeData['city'], $college->getCity());
        $this->assertEquals($collegeData['state'], $college->getState());
        $this->assertEquals($collegeData['zip'], $college->getZip());
        $this->assertInstanceOf('Mrss\Entity\System', $college->getSystem());
    }

    /**
     * Test address formatting.
     */
    public function testGetFullAddress()
    {
        $college = new College();

        $college->setAddress('123 Main');
        $college->setCity('Overland Park');
        $college->setState('KS');
        $college->setZip('66101');

        $expected = "123 Main<br>\nOverland Park, KS 66101";

        $this->assertEquals($expected, $college->getFullAddress());
    }

    public function testSetUsers()
    {
        $college = new College;
        $college->setUsers('placeholder');

        $this->assertEquals('placeholder', $college->getUsers());
    }

    public function testSetSubscriptions()
    {
        $college = new College;
        $college->setSubscriptions('placeholder');

        $this->assertEquals('placeholder', $college->getSubscriptions());
    }

    public function testGetObservationForYear()
    {
        $college = new College();

        $observationsMock = $this->getMock(
            '\Doctrine\Common\Collections\ArrayCollection',
            array('matching', 'first', 'count')
        );

        $observationsMock->expects($this->once())
            ->method('matching')
            ->will($this->returnValue($observationsMock));

        // Return count of 1
        $observationsMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        // Return one observation
        $observationsMock->expects($this->once())
            ->method('first')
            ->will($this->returnValue('placeholder'));

        $college->setObservations($observationsMock);

        $result = $college->getObservationForYear(2013);

        $this->assertEquals('placeholder', $result);
    }

    public function testGetObservationForYearEmpty()
    {
        $college = new College();

        $observationsMock = $this->getMock(
            '\Doctrine\Common\Collections\ArrayCollection',
            array('matching', 'count')
        );

        $observationsMock->expects($this->once())
            ->method('matching')
            ->will($this->returnValue($observationsMock));

        // Return count of 1
        $observationsMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $college->setObservations($observationsMock);

        $result = $college->getObservationForYear(2013);

        $this->assertNull($result);
    }

    public function testGetCompletionPercentage()
    {
        $college = new College();

        $observationMock = $this->getMock('Mrss\Entity\Observation');

        $observationsMock = $this->getMock(
            '\Doctrine\Common\Collections\ArrayCollection',
            array('matching', 'first', 'count')
        );

        $observationsMock->expects($this->once())
            ->method('matching')
            ->will($this->returnValue($observationsMock));

        // Return count of 1
        $observationsMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        // Return one observation
        $observationsMock->expects($this->once())
            ->method('first')
            ->will($this->returnValue($observationMock));

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getCompletionPercentage')
        );
        $studyMock->expects($this->once())
            ->method('getCompletionPercentage')
            ->will($this->returnValue('placeholder2'));

        $college->setObservations($observationsMock);

        $result = $college->getCompletionPercentage(2013, $studyMock);

        $this->assertEquals('placeholder2', $result);
    }

    public function testGetCompletionPercentageEmpty()
    {
        $college = new College();

        $observationMock = $this->getMock('Mrss\Entity\Observation');

        $observationsMock = $this->getMock(
            '\Doctrine\Common\Collections\ArrayCollection',
            array('matching', 'first', 'count')
        );

        $observationsMock->expects($this->once())
            ->method('matching')
            ->will($this->returnValue($observationsMock));

        // Return count of 0
        $observationsMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getCompletionPercentage')
        );

        $college->setObservations($observationsMock);

        $result = $college->getCompletionPercentage(2013, $studyMock);

        $this->assertEquals(0, $result);
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
                    'address2' => 'OCB 204',
                    'city' => 'Highland',
                    'state' => 'KS',
                    'zip' => 66035
                )
            )
        );
    }
}
