<?php
/**
 * Test the observation entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use PHPUnit_Framework_TestCase;

/**
 * Class ObservationTest
 *
 * @package MrssTest\Entity
 */
class ObservationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testUserInitialState()
    {
        $observation = new Observation();

        $this->assertNull(
            $observation->getYear(),
            '"year" should initially be null'
        );

        $this->assertNull(
            $observation->getCollege(),
            '"college" should initially be null'
        );

        $this->assertNull(
            $observation->getCipCode(),
            '"cipCode" should initially be null'
        );
    }
}
