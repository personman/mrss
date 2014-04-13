<?php
/**
 * Test the IpedsInstitution entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\IpedsInstitution;
use PHPUnit_Framework_TestCase;

/**
 * Class IpedsInstitutionTest
 *
 * @package MrssTest\Model
 */
class IpedsInstitutionTest extends PHPUnit_Framework_TestCase
{
    /** @var  IpedsInstitution */
    protected $institution;

    public function setUp()
    {
        $this->institution = new IpedsInstitution;
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\IpedsInstitution', $this->institution);
    }

    public function testSetters()
    {
        $this->institution->setId(5);
        $this->assertEquals(5, $this->institution->getId());

        $this->institution->setName('JCCC');
        $this->assertEquals('JCCC', $this->institution->getName());

        $this->institution->setIpeds('155210');
        $this->assertEquals('155210', $this->institution->getIpeds());

        $this->institution->setAddress('12345 College Blvd.');
        $this->assertEquals('12345 College Blvd.', $this->institution->getAddress());

        $this->institution->setCity('Overland Park');
        $this->assertEquals('Overland Park', $this->institution->getCity());

        $this->institution->setState('KS');
        $this->assertEquals('KS', $this->institution->getState());

        $this->institution->setZip('12345');
        $this->assertEquals('12345', $this->institution->getZip());
    }
}
