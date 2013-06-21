<?php
/**
 * Test the observation model
 */
namespace MrssTest\Model;

use Mrss\Model\Observation;

/**
 * Class ObservationTest
 *
 * @package MrssTest\Model
 */
class ObservationTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\Observation
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Observation();
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\Observation', $this->model);
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

    /**
     * Find by college, year, cipCode
     */
    public function testFindOne()
    {
        $collegeId = 2;
        $year = 2013;
        $cipCode = 1;

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

        $result = $this->model->findOne($collegeId, $year, $cipCode);

        $this->assertEquals('placeholder', $result);
    }

    /**
     * Find them all
     */
    public function testFindAll()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findAll', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue('placeholder'));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $result = $this->model->findAll();

        $this->assertEquals('placeholder', $result);
    }

    /**
     * Find data for a chart
     */
    public function testFindForChart()
    {
        $statementMock = $this->getMock(
            'Doctrine\DBAL\Query',
            array('fetchAll')
        );
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue('placeholder'));

        $qbMock = $this->getMock(
            'Doctrine\DBAL\Query\QueryBuilder',
            array('select', 'from', 'groupBy', 'execute'),
            array(),
            '',
            false
        );
        $qbMock->expects($this->once())
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
            ->will($this->returnValue($qbMock));

        $emMock = $this->getEmMock(array('getConnection'));

        $emMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $this->model->setEntityManager($emMock);

        $result = $this->model->findForChart('no_tot_emp_rel_perc', array(1));

        $this->assertEquals('placeholder', $result);
    }

    /**
     * Test saving an observation entity
     */
    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\Observation);
    }
}
