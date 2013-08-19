<?php
/**
 * Test the payment model
 */
namespace MrssTest\Model;

use Mrss\Model\Payment;

/**
 * Class PaymentTest
 *
 * @package MrssTest\Model
 */
class PaymentTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\Payment
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Payment;
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Model\Payment', $this->model);
    }

    /**
     * Find by trans id
     */
    public function testFindByTransId()
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

        $result = $this->model->findByTransId(5);

        $this->assertEquals('placeholder', $result);
    }

    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\Payment);
    }
}
