<?php
/**
 * Test the benchmarkGroup model
 */
namespace MrssTest\Model;

use Mrss\Model\BenchmarkGroup;

/**
 * Class BenchmarkGroupTest
 *
 * @package MrssTest\Model
 */
class BenchmarkGroupTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\BenchmarkGroup
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\BenchmarkGroup;
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\BenchmarkGroup', $this->model);
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

    public function testFindOneByShortName()
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
            ->with(
                $this->equalTo(array('shortName' => 'test_short_name'))
            )
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findOneByShortName('test_short_name');

        $this->assertEquals('placeholder', $result);

    }

    public function testFindOneByName()
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
            ->with(
                $this->equalTo(array('name' => 'Test name'))
            )
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findOneByName('Test name');

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

        $this->model->save(new \Mrss\Entity\BenchmarkGroup);
    }

    public function testGetMaxSequence()
    {
        $benchmarkGroupMock = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getSequence')
        );
        $benchmarkGroupMock->expects($this->once())
            ->method('getSequence')
            ->will($this->returnValue(99));

        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );
        $repoMock->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($benchmarkGroupMock));

        $this->model->setRepository($repoMock);

        $this->assertEquals(99, $this->model->getMaxSequence());
    }
}
