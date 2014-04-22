<?php

namespace MrssTest\Service;

use Mrss\Entity\Observation;
use Mrss\Service\ObservationAudit;

class ObservationAuditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObservationAudit
     */
    protected $service;

    public function setUp()
    {
        $this->service = new ObservationAudit();
    }

    public function tearDown()
    {
        unset($this->service);
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
            )
        );
    }
}
