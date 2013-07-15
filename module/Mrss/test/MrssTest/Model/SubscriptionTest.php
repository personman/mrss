<?php
/**
 * Test the subscription model
 */
namespace MrssTest\Model;

use Mrss\Model\Study;

/**
 * Class SubscriptionTest
 *
 * @package MrssTest\Model
 */
class SubscriptionTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\Subscription
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Subscription();
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\Subscription', $this->model);
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

    public function testFindOne()
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

        $result = $this->model->findOne(2013, 1, 1);

        $this->assertEquals('placeholder', $result);
    }

    public function testFindByStudyAndYear()
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

        $result = $this->model->findByStudyAndYear(5, 2013);

        $this->assertEquals('placeholder', $result);
    }

    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\Subscription());
    }

    public function testFindCurrentSubscription()
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

        $studyMock = $this->getMock(
            'Mrss\Entity\Study',
            array('getCurrentYear', 'getId')
        );
        $studyMock->expects($this->once())
            ->method('getCurrentYear')
            ->will($this->returnValue(2013));
        $studyMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(3));

        $sub = $this->model->findCurrentSubscription($studyMock, 1);
        $this->assertEquals('placeholder', $sub);
    }
}
