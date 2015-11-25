<?php

namespace MrssTest\Service;

use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use Mrss\Entity\User;
use Mrss\Entity\Study;
use Mrss\Service\ObservationAudit;

class ObservationAuditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObservationAudit
     */
    protected $service;

    protected $changeSetModel;

    protected $benchmarkModel;

    public function setUp()
    {
        $this->service = new ObservationAudit();

        // Mock the changeSet model
        $this->changeSetModel = $this->getMock(
            '\Mrss\Model\ChangeSet',
            array('save')
        );
        $this->service->setChangeSetModel($this->changeSetModel);

        // Mock the benchmark model and the benchmark it returns
        $benchmarkMock = $this->getMock('\Mrss\Entity\Benchmark');

        $emMock = $this->getMock(
            'Doctrine\ORM\EntityManager',
            array('getReference'),
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue(new User));

        $this->benchmarkModel = $this->getMock(
            '\Mrss\Model\Benchmark',
            array('findOneByDbColumn', 'getEntityManager')
        );

        $this->benchmarkModel->expects($this->any())
            ->method('findOneByDbColumn')
            ->will($this->returnValue($benchmarkMock));

        $this->benchmarkModel->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($emMock));

        $this->service->setBenchmarkModel($this->benchmarkModel);

        $this->service->setUser(new User());
        $this->service->setImpersonator(new User());
        $this->service->setStudy(new Study());

    }

    public function tearDown()
    {
        unset($this->service);
        unset($this->changeSetModel);
        unset($this->benchmarkModel);
    }


    /**
     * @param $old
     * @param $new
     * @param $expectedChanges
     * @dataProvider getComparisons
     */
    public function testCompareObservations($old, $new, $expectedChanges)
    {
        $oldObservation = new Observation();
        $oldObservation->populate($old);

        $newObservation = new Observation();
        $newObservation->populate($new);

        $this->assertEquals(
            $expectedChanges,
            $this->service->compare($oldObservation, $newObservation)
        );
    }

    public function getComparisons()
    {
        return array(
            // One
            array(
                // $old
                array(
                    'inst_exec_num' => 22,
                    'inst_o_cost' => 500,
                    'op_exp_inst' => 33
                ),
                // $new
                array(
                    'inst_exec_num' => 22,
                    'inst_o_cost' => 400,
                    'op_exp_inst' => 33
                ),
                // $expectedChanges
                array(
                    'inst_o_cost' => array(
                        'old' => 500,
                        'new' => 400
                    )
                )
            ),
            // Two
            array(
                // $old
                array(
                    'inst_exec_num' => 22,
                    'inst_o_cost' => 500,
                    'op_exp_inst' => null
                ),
                // $new
                array(
                    'inst_exec_num' => 21,
                    'inst_o_cost' => 400,
                    'op_exp_inst' => 33
                ),
                // $expectedChanges
                array(
                    'inst_o_cost' => array(
                        'old' => 500,
                        'new' => 400
                    ),
                    'inst_exec_num' => array(
                        'old' => 22,
                        'new' => 21
                    ),
                    'op_exp_inst' => array(
                        'old' => null,
                        'new' => 33
                    )
                )
            ),
            // Three (no changes)
            array(
                // $old
                array(
                    'inst_exec_num' => 22,
                    'inst_o_cost' => 500,
                    'op_exp_inst' => null
                ),
                // $new
                array(
                    'inst_exec_num' => 22,
                    'inst_o_cost' => 500,
                    'op_exp_inst' => null
                ),
                // $expectedChanges
                array()
            )
        );
    }

    /**
     * @param $old
     * @param $new
     * @param $expectedChanges
     * @internal param $user
     * @dataProvider getComparisons
     *
    public function testLogChanges($old, $new, $expectedChanges)
    {
        $oldObservation = new Observation();
        $oldObservation->populate($old);

        $newObservation = new Observation();
        $newObservation->populate($new);

        // Expect the save
        if (!empty($expectedChanges)) {
            $this->changeSetModel->expects($this->once())
                ->method('save');
        }

        $changeSet = $this->service->logChanges(
            $oldObservation,
            $newObservation,
            'dataEntry'
        );

        if (!empty($expectedChanges)) {
            //$this->assertSame($this->service->getUser(), $changeSet->getUser());

            $changes = $changeSet->getChanges();
            $this->assertEquals(count($expectedChanges), count($changes));
        } else {
            $this->assertNull($changeSet);
        }

    }*/

    /**
     * @param $old
     * @param $new
     * @param $expectedChanges
     * @dataProvider getSubObComparisons
     */
    public function testLogSubObservationChanges($old, $new, $expectedChanges)
    {
        $oldSubOb = new SubObservation;
        $oldSubOb->populate($old);

        $newSubOb = new SubObservation;
        $newSubOb->populate($new);

        $observation = new Observation;
        $newSubOb->setObservation($observation);

        $changeSet = $this->service->logSubObservationChanges(
            $oldSubOb,
            $newSubOb,
            'dataEntry'
        );

        if (!empty($expectedChanges)) {
            $this->assertSame($this->service->getUser(), $changeSet->getUser());

            $changes = $changeSet->getChanges();
            $this->assertEquals(count($expectedChanges), count($changes));
        } else {
            $this->assertNull($changeSet);
        }

    }

    public function getSubObComparisons()
    {
        return array(
            array(
                // Old
                array(
                    'inst_cost_full_teaching' => 20
                ),
                // New
                array(
                    'inst_cost_full_teaching' => 21
                ),
                // Expected changes
                array(
                    'inst_cost_full_teaching' => array(
                        'old' => 20,
                        'new' => 21
                    )
                )
            )
        );
    }
}
