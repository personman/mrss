<?php

namespace MrssTest\Model;

use Mrss\Model\Setting;

/**
 * Class SettingTest
 *
 * @package MrssTest\Model
 */
class SettingTest extends ModelTestAbstract
{
    /**
     * @var \Mrss\Model\Setting
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Mrss\Model\Setting;
    }

    public function tearDown()
    {
        unset($this->model);
    }

    public function testFindOneByIdentifier()
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

        $result = $this->model->findOneByIdentifier('test');

        $this->assertEquals('placeholder', $result);
    }

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

    public function testSetValueForIdentifier()
    {
        $settingMock = $this->getMock(
            'Mrss\Entity\Setting',
            array('setIdentifier', 'setValue', 'getValue')
        );
        $settingMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('expected value'));

        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        $repoMock->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($settingMock));

        $emMock = $this->getEmMock();

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($emMock);

        $this->model->setValueForIdentifier('test2', 'expected value');
        $this->assertEquals(
            'expected value',
            $this->model->getValueForIdentifier('test2')
        );
    }

    public function testSetValueForIdentifierWithNonexistent()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        // The first time we look for the setting, it's not there:
        $repoMock->expects($this->at(0))
            ->method('findOneBy')
            ->will($this->returnValue(null));

        // The second time, it returns our mock
        $settingMock = $this->getMock(
            'Mrss\Entity\Setting',
            array('setIdentifier', 'setValue', 'getValue')
        );
        $settingMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('expected value'));

        $repoMock->expects($this->at(1))
            ->method('findOneBy')
            ->will($this->returnValue($settingMock));

        $emMock = $this->getEmMock();

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($emMock);

        $this->model->setValueForIdentifier('test2', 'expected value');
        $this->assertEquals(
            'expected value',
            $this->model->getValueForIdentifier('test2')
        );
    }

    public function testGetValueForIdentifierNonexistent()
    {
        $repoMock = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('findOneBy', 'getUnitOfWork'),
            array(),
            '',
            false
        );

        // The first time we look for the setting, it's not there:
        $repoMock->expects($this->at(0))
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $this->model->setRepository($repoMock);
        $this->model->setEntityManager($this->getEmMock());

        $this->assertNull($this->model->getValueForIdentifier('not-real'));
    }

    public function testSave()
    {
        $emMock = $this->getEmMock();

        // Expect a call to persist()
        $emMock->expects($this->once())
            ->method('persist');

        $this->model->setEntityManager($emMock);

        $this->model->save(new \Mrss\Entity\Setting);
    }
}
