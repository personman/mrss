<?php
/**
 * Test the observation entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Observation;
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

    public function testSetters()
    {
        $observation = new Observation();

        // setYear
        $observation->setYear('2008');
        $this->assertEquals('2008', $observation->getYear());

        // setId
        $observation->setId(25);
        $this->assertEquals(25, $observation->getId());

        $observation->setCipCode('5555.55');
        $this->assertEquals('5555.55', $observation->getCipCode());

        $college = $this->getMock('Mrss\Entity\College');
        $observation->setCollege($college);
        $this->assertSame($college, $observation->getCollege());
    }

    /**
     * @dataProvider getFields
     */
    public function testDynamicGetter($field)
    {
        $observation = new Observation();

        $this->assertNull($observation->get($field));
    }

    /**
     * @dataProvider getInvalidFields
     * @expectedException Mrss\Entity\Exception\InvalidBenchmarkException
     */
    public function testDynamicGetterWithInvalid($field)
    {
        $observation = new Observation();

        $observation->get($field);
    }

    /**
     * Test dynamic setter
     *
     * @param $field
     * @param $value
     * @dataProvider getFieldsAndValues
     */
    public function testDynamicSetter($field, $value)
    {
        $observation = new Observation();

        $observation->set($field, $value);

        $this->assertEquals($value, $observation->get($field));
    }

    /**
     * @param $field
     * @dataProvider getInvalidFields
     * @expectedException Mrss\Entity\Exception\InvalidBenchmarkException
     */
    public function testDynamicSetterWithInvalid($field)
    {
        $observation = new Observation();

        $observation->set($field, 5);
    }


    /**
     * Some valid benchmark fields
     *
     * @return array
     */
    public function getFields()
    {
        return array(
            array('tot_undup_cr_hd'),
            array('tot_fte_career_staff'),
            array('tot_fte_counc_adv_staff')
        );
    }

    /**
     * Some invalid benchmark fields
     *
     * @return array
     */
    public function getInvalidFields()
    {
        return array(
            array('lkasdfkasdf'),
            array('this_is_not_real')
        );
    }

    /**
     * Some valid benchmark fields and values
     *
     * @return array
     */
    public function getFieldsAndValues()
    {
        return array(
            array('tot_undup_cr_hd', 5),
            array('tot_fte_career_staff', 6.3),
            array('tot_fte_counc_adv_staff', -44),
            array('tot_fte_counc_adv_staff', 10555555)
        );
    }
}
