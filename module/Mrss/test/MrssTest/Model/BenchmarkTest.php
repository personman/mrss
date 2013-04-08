<?php
/**
 * Test the benchmark model
 */
namespace MrssTest\Model;

use Mrss\Model\Benchmark;
use PHPUnit_Framework_TestCase;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Model
 */
class BenchmarkeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Mrss\Model\Benchmark
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Benchmark;
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\Benchmark', $this->model);
    }

    public function testFindAll()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findAll();

        $this->assertEquals('placeholder', $result);
    }

    public function testFindOneByDbColumn()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findOneByDbColumn('111111');

        $this->assertEquals('placeholder', $result);
    }

    /**
     * Find by id
     */
    public function testFind()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('find', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo(5))
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->find(5);

        $this->assertEquals('placeholder', $result);
    }

    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\Benchmark);
    }

    protected function getEmMock()
    {
        $repositoryMock = $this->getMock(
            'Doctrine\Orm\Repository',
            array('findOneBy')
        );

        $emMock  = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'),
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }
}
