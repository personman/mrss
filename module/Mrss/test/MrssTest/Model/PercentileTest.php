<?php
/**
 * Test the percentile model
 */
namespace MrssTest\Model;

use Mrss\Model\Percentile;

/**
 * Class PercentileTest
 *
 * @package MrssTest\Model
 */
class PercentileTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\Percentile
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Percentile;
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\Percentile', $this->model);
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

        $this->model->save(new \Mrss\Entity\Percentile);
    }

    /*public function testDeleteByStudyAndYear()
    {
        $emMock = $this->getEmMock();

        // Mock the query builder
        $queryMock = $this->getMock(
            'Doctrine\ORM\QueryBuilder',
            array('setParameter', 'execute', 'getConfiguration'),
            array($emMock),
            '',
            true
        );

        // Expect a call to createQuery
        $emMock->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($queryMock));

        $this->model->setEntityManager($emMock);

        $this->model->deleteByStudyAndYear(2, 2013);
    }*/
}
