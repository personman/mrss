<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class PeerComparison extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('peerComparison');

        $this->add(
            array(
                'name' => 'reportingPeriod',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Reporting Period'
                ),
                'attributes' => array(
                    'id' => 'reportingPeriod',
                    'options' => $this->getYearsWithData()
                )
            )
        );

        $this->add(
            array(
                'name' => 'benchmarks',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Benchmark(s)'
                ),
                'attributes' => array(
                    'id' => 'benchmarks',
                    'options' => $this->getBenchmarks(),
                    'multiple' => 'multiple'
                )
            )
        );

        $this->add(
            array(
                'name' => 'peers',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Peer Institutions'
                ),
                'attributes' => array(
                    'id' => 'peers',
                    'options' => $this->getPeers(),
                    'multiple' => 'multiple',
                    'rows' => 20,
                    'cols' => 80
                )
            )
        );

        $this->add($this->getButtonFieldset('Continue'));
    }

    public function getYearsWithData()
    {
        return array('2013' => '2013');
    }

    public function getBenchmarks()
    {
        return array('test benchmark', 'test benchmark 2', 'test benchmark 3');
    }

    public function getPeers()
    {
        return array();
    }
}
