<?php
/**
 * Test the outlier entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Outlier;
use PHPUnit_Framework_TestCase;

/**
 * Class OutlierTest
 *
 * @package MrssTest\Model
 */
class OutlierTest extends PHPUnit_Framework_TestCase
{
    /** @var  Outlier */
    protected $outlier;

    public function setUp()
    {
        $this->outlier = new Outlier();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf('\Mrss\Entity\Outlier', $this->outlier);
    }

    public function testSetters()
    {
        $this->outlier->setId(5);
        $this->assertEquals(5, $this->outlier->getId());

        $benchmark = $this->getMock('\Mrss\Entity\Benchmark', array());
        $this->outlier->setBenchmark($benchmark);
        $this->assertSame($benchmark, $this->outlier->getBenchmark());

        $this->outlier->setValue(5);
        $this->assertEquals(5, $this->outlier->getValue());

        $this->outlier->setProblem('low');
        $this->assertEquals('low', $this->outlier->getProblem());

        $study = $this->getMock('\Mrss\Entity\Study', array());
        $this->outlier->setStudy($study);
        $this->assertSame($study, $this->outlier->getStudy());

        $college = $this->getMock('\Mrss\Entity\College', array());
        $this->outlier->setCollege($college);
        $this->assertSame($college, $this->outlier->getCollege());

        $this->outlier->setYear(2014);
        $this->assertEquals(2014, $this->outlier->getYear());

    }
}
