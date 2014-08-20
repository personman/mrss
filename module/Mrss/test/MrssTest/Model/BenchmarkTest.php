<?php
/**
 * Test the benchmark model
 */
namespace MrssTest\Model;

use Mrss\Model\Benchmark;

/**
 * Class BenchmarkTest
 *
 * @package MrssTest\Model
 */
class BenchmarkTest extends ModelTestAbstract
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

    public function testFindComputed()
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
            ->will($this->returnValue(array()));

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));


        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findComputed($studyMock);

        $this->assertEquals(array(), $result);
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

    public function testFindOneByDbColumnAndGroup()
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

        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getShortName')
        );

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model
            ->findOneByDbColumnAndGroup('111111', $benchmarkGroupMock);

        $this->assertEquals('placeholder', $result);
    }

    public function testFindOneByDbColumnAndGroupEmpty()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getShortName')
        );

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model
            ->findOneByDbColumnAndGroup('111111', $benchmarkGroupMock);

        $this->assertEquals(null, $result);
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

    public function testGetMaxSequence()
    {
        $lastBenchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getSequence')
        );

        $lastBenchmarkMock->expects($this->once())
            ->method('getSequence')
            ->will($this->returnValue(5));


        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($lastBenchmarkMock));

        $this->model->setRepository($repoMock);

        $this->assertEquals(5, $this->model->getMaxSequence());
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

    public function testGetCompletionPercentagesWithNoYear()
    {
        $this->assertEquals(
            array(),
            $this->model->getCompletionPercentages('something', null)
        );

    }

    public function testGetCompletionPercentage()
    {
        $percentages = array(
            array(
                'year' => 2013,
                'percentage' => 50.1
            )
        );

        $statementMock = $this->getMock(
            'Doctrine\DBAL\Statement',
            array('fetchAll'),
            array(),
            '',
            false
        );
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($percentages));

        $queryBuilderMock = $this->getMock(
            'Doctrine\DBAL\Query\QueryBuilder',
            array('select', 'from', 'where', 'groupBy', 'execute'),
            array(),
            '',
            false
        );
        $queryBuilderMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($statementMock));

        $connectionMock = $this->getMock(
            'Doctrine\DBAL\Connection',
            array('createQueryBuilder'),
            array(),
            '',
            false
        );
        $connectionMock->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilderMock));

        $emMock = $this->getEmMock(array('getConnection'));
        $emMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $this->model->setEntityManager($emMock);

        $result =$this->model->getCompletionPercentages('someColumn', array(2013));
        $this->assertEquals(array('2013' => 50.1), $result);
    }

    public function testGetCompletionPercentageWithException()
    {
        $statementMock = $this->getMock(
            'Doctrine\DBAL\Statement',
            array('fetchAll'),
            array(),
            '',
            false
        );
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->throwException(new \Exception()));

        $queryBuilderMock = $this->getMock(
            'Doctrine\DBAL\Query\QueryBuilder',
            array('select', 'from', 'where', 'groupBy', 'execute'),
            array(),
            '',
            false
        );
        $queryBuilderMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($statementMock));

        $connectionMock = $this->getMock(
            'Doctrine\DBAL\Connection',
            array('createQueryBuilder'),
            array(),
            '',
            false
        );
        $connectionMock->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilderMock));

        $emMock = $this->getEmMock(array('getConnection'));
        $emMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $this->model->setEntityManager($emMock);

        $result =$this->model->getCompletionPercentages('someColumn', array(2013));
        $this->assertEquals(array(), $result);
    }
}
