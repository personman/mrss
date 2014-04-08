<?php
/**
 * Test the subobservation entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\SubObservation;
use PHPUnit_Framework_TestCase;

/**
 * Class SubObservationTest
 *
 * @package MrssTest\Entity
 */
class SubObservationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubObservation
     */
    protected $subObservation;

    public function setUp()
    {
        $this->subObservation = new SubObservation();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\SubObservation', $this->subObservation);
    }

    public function testGetters()
    {
        $this->subObservation->setId(5);
        $this->assertEquals(5, $this->subObservation->getId());

        $this->subObservation->setName('English');
        $this->assertEquals('English', $this->subObservation->getName());

        $observationMock = $this->getMock('Mrss\Entity\Observation');
        $this->subObservation->setObservation($observationMock);
        $this->assertSame($observationMock, $this->subObservation->getObservation());
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
        $this->subObservation->set($field, $value);

        $this->assertEquals($value, $this->subObservation->get($field));
    }

    /**
     * @param $field
     * @dataProvider getInvalidFields
     * @expectedException \Mrss\Entity\Exception\InvalidBenchmarkException
     */
    public function testDynamicSetterWithInvalid($field)
    {
        $this->subObservation->set($field, 5);
    }

    /**
     * @param $field
     * @dataProvider getInvalidFields
     * @expectedException \Mrss\Entity\Exception\InvalidBenchmarkException
     */
    public function testDynamicGetterWithInvalid($field)
    {
        $this->subObservation->get($field, 5);
    }

    public function testGetArrayCopy()
    {
        $this->subObservation->setName('English');
        $this->subObservation->set('inst_cost_full_expend', 52);

        $arrayCopy = $this->subObservation->getArrayCopy();

        $this->assertEquals('English', $arrayCopy['name']);
        $this->assertEquals(52, $arrayCopy['inst_cost_full_expend']);
    }

    public function testPopulate()
    {
        $this->subObservation->populate(
            array(
                'inst_cost_full_expend' => 55
            )
        );

        $this->assertEquals(55, $this->subObservation->get('inst_cost_full_expend'));
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
            array('inst_cost_full_num', 5),
            array('inst_cost_full_cred_hr', 6.3),
            array('inst_cost_part_num', -44),
            array('inst_cost_non_labor_oper_cost', 10555555),
            array('inst_cost_part_expend', '')
        );
    }

}
