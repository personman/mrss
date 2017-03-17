<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\BenchmarkSelect;

class StructureController extends AbstractActionController
{
    public function indexAction()
    {

    }

    public function editAction()
    {
        // @todo: get current subscription so this supports sections
        $subscription = null;
        $benchmarks = $this->currentStudy()->getStructuredBenchmarks(false, 'dbColumn', $subscription, false);
        $benchmarkForm = new BenchmarkSelect($benchmarks);

        return array(
            'benchmarkForm' => $benchmarkForm
        );
    }
}
