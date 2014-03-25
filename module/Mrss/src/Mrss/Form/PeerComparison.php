<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

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
                    'label' => 'Peer Institutions',
                    'help-block' => 'Select at least 5 peer institutions.'
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

        // Disable the inArray validator since those options get
        // populated dynamically
        $this->get('peers')->setDisableInArrayValidator(true);
        $this->get('benchmarks')->setDisableInArrayValidator(true);

        $this->setInputFilter($this->getInputFilterSetup());
    }

    public function getInputFilterSetup()
    {
        $filter = new InputFilter();

        // State is not required
        $peers = new Input('peers');
        $peers->getValidatorChain()->attach(new \Mrss\Validator\MinimumSelected(5));
        $filter->add($peers);

        return $filter;
    }

    public function getYearsWithData()
    {
        return array('2013' => '2013');
    }

    public function getBenchmarks()
    {
        return array();
    }

    public function getPeers()
    {
        return array();
    }
}
