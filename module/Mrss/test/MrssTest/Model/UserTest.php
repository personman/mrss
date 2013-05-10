<?php
/**
 * Test the subscription model
 */
namespace MrssTest\Model;

use Mrss\Model\User;

/**
 * Class UserTest
 *
 * @package MrssTest\Model
 */
class UserTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\User
     */
    protected $model;

    public function setUp()
    {
        $this->model = new User();
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\User', $this->model);
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

    public function testFindOneByEmail()
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

        $result = $this->model->findOneByEmail('test@test.com');

        $this->assertEquals('placeholder', $result);
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

    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\User());
    }
}
